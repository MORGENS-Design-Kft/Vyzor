# Vyzor AI architektúra — fejlesztői útmutató

Ez a dokumentum végigvezeti a Vyzor AI-alapú riportgenerálási rendszerét: mi hol él, mit csinál, és **hogyan kell hozzáadni egy újat** anélkül, hogy az egész flow-t fejből kellene tudnod.

> **Olvasási sorrend ajánlás**: 1. *TL;DR* → 2. *A teljes flow egy ábrában* → 3. *Az 5 építőelem* → utána már a *Recipes* szakaszra ugorhatsz, amikor konkrét feladatod van.

---

## TL;DR

A Vyzor AI rétege **5 építőelemből** áll:

| Építőelem | Mi a dolga | Hol él |
|---|---|---|
| **Agent** | Az AI személyiség (system prompt + tool lista) | `app/Modules/Ai/Agents/` |
| **Tool** | Function-calling endpoint az AI-nak (project-szkópú adatlekérés) | `app/Modules/{ForrásModul}/Tools/` |
| **Context** | DB-ben tárolt prompt-darab (preset / system / instruction) | `app/Modules/Ai/Contexts/` + `resources/ai-prompts/` + `database/seeders/AiContextSeeder.php` |
| **Gateway** | HTTP transport az AI provider felé (OpenAI/Anthropic/...) | `app/Modules/Ai/Gateway/` (Vyzor-patched) + `vendor/laravel/ai` (alap) |
| **Service** | Az orchestrator — összerakja a promptot és lefuttatja az agent-et | `app/Modules/Reports/Services/ReportGeneratorService.php` |

A folyamat: felhasználó kér egy riportot → `Report` rekord létrejön → queue job futtatja a Service-t → Service kiválaszt egy Agent-et a riport "flavor"-je alapján → Agent összeállítja a promptot a Context-ekből → Agent meghívja az AI-t a Gateway-en át → AI válasz mentve a Report-ba.

---

## A teljes flow egy ábrában

```
┌─────────────────┐
│  Livewire form  │  pl. ⚡ga-report-tab.blade.php — felhasználó preset-et + dátumot választ
└────────┬────────┘
         │ Report::create([..., status: PENDING])
         │ GenerateAiReport::dispatch($report)
         ▼
┌─────────────────────────────────────────┐
│  GenerateAiReport (Queue Job)           │  app/Modules/Reports/Jobs/GenerateAiReport.php
│  • timeout: 300s, tries: 1              │
│  • status → GENERATING                  │
└────────┬────────────────────────────────┘
         │ ReportGeneratorService::generate($report)
         ▼
┌─────────────────────────────────────────────────────────────┐
│  ReportGeneratorService                                     │
│  ────────────────                                           │
│  1. loadPreset(report)        →  AiContext (preset)          │
│  2. resolveFlavor(preset)     →  'page' | 'ga' | 'clarity'  │
│  3. resolveAgent(flavor)      →  PageAnalyst | ReportAnalyst │
│  4. buildPrompt(flavor)       →  string  (preset + custom + │
│                                  data + language + format)   │
│  5. agent->prompt(prompt)     →  AI hívás                    │
│  6. validate non-empty                                       │
│  7. Report->update(content, COMPLETED) | (FAILED on throw)  │
└────────┬────────────────────────────────────────────────────┘
         │ Promptable::prompt() — agent-attribute Timeout(300)
         ▼
┌──────────────────────────────────────────────────────┐
│  Agent (ReportAnalyst / PageAnalyst)                 │
│  • instructions() — system context (DB-ből)          │
│  • tools() — function-calling tool-ok listája        │
└────────┬─────────────────────────────────────────────┘
         │ TextProvider::prompt(AgentPrompt)
         ▼
┌─────────────────────────────────────────────────────┐
│  TimeoutAwareOpenAiGateway (Vyzor-patched)          │  app/Modules/Ai/Gateway/
│  ─────────────────                                  │
│  1. POST /v1/responses  →  prompt + tool defs       │
│  2. ha OpenAI tool_call-t kér:                      │
│       — tool->handle(args)  →  tool eredmény         │
│       — POST /v1/responses (tool result + previous) │
│       — loop ha még kell                            │
│  3. utolsó válasz → TextResponse                    │
└─────────────────────────────────────────────────────┘
```

---

## Az 5 építőelem részletesen

### 1. Agent — a "személyiség"

Az agent egy **PHP osztály**, ami implementálja a `Laravel\Ai\Contracts\Agent` interface-t. Két dolgot mond meg:

- **`instructions()`** — a system prompt szövege (általában DB-ből egy `AiContext` SYSTEM-rekord)
- **`tools()`** — milyen function-calling tool-okat lát a modell ehhez a futáshoz

Plusz egy attribute-ot is hordoz: **`#[Timeout(300)]`** — meddig várhat egy `prompt()` hívás. **Fontos**: a Vyzor `TimeoutAwareOpenAiGateway` ezt szigorúan betartja a tool-call loopban is (lásd később a *Patches* szakaszt).

**Jelenlegi agent-ek**:

| Agent | Mire való | System context slug |
|---|---|---|
| `ReportAnalyst` | Analytics riportok (Clarity + GA) — `instructionsSlug` paraméter dönti el melyik | `report-analyst-instructions` (Clarity) / `ga-analyst-instructions` (GA) |
| `PageAnalyst` | Egy konkrét URL elemzése (HTML lekérése + AI értékelés) | `page-analyst-instructions` |

**Konvenció**: az agent kapja a `Project`-et a konstruktorban, a `tools()` ennek alapján dönti el milyen tool-okat exponál.

```php
public function tools(): iterable
{
    if ($this->project && $this->project->hasGoogleAnalytics()) {
        return [new GoogleAnalyticsTool(
            project: $this->project,
            query:   app(GoogleAnalyticsQueryService::class),
        )];
    }
    return [];
}
```

> ⚠ **Figyelem**: `tools()` **plain array-t** adjon vissza, ne Generator-t (`yield`). A Laravel/AI gateway `array` típushoz kötött, és egy `yield` runtime-ban elszáll.

### 2. Tool — function-calling endpoint

A tool egy olyan PHP osztály, amit a modell **menet közben hívhat** ha mélyebb adatokra van szüksége. Pl. a `GoogleAnalyticsTool` lehetővé teszi, hogy az AI kérjen egy szűrt riportot mobile-felhasználókról, miközben az összegző riportot írja.

**Vyzor base class**: `App\Modules\Ai\Tools\ProjectScopedTool` — ezt **mindenki örökli**, és a következőket adja:
- **Project-binding**: a tool konstruktorban kap egy `Project`-et, és sose férhet hozzá másikhoz
- **JSON response shape**: minden válasz `string` JSON, az error is `{"error": "..."}` formában
- **Try/catch wrapper**: az `execute()` metódusból dobott exception automatikusan error JSON-ná válik

A subclass csak ezt implementálja:

```php
class GoogleAnalyticsTool extends ProjectScopedTool
{
    public function description(): string { /* mit tud, mikor használja az AI */ }
    public function schema(JsonSchema $schema): array { /* paraméterek típusai */ }
    
    protected function execute(Request $request): array
    {
        // a lényeg — return egy array-t (JSON-encodable bármi)
        // throw → automatikus {"error": ...} response
    }
}
```

> 🔑 **OpenAI strict mode-os schema**: a Laravel/AI `mapTool()`-ja `strict: true`-t küld OpenAI-nak. Ez azt jelenti **minden** property-t tegyél `required()`-be, opcionális mezőkre `nullable()`-t, az inner `object`-ekre `withoutAdditionalProperties()`-t, az `array`-ekre `items()`-t. Ha hiányzik bármi → `Invalid schema for function 'XYZ'` 400-as hiba.

**Jelenlegi tool-ok**:

| Tool | Hol él | Mit csinál |
|---|---|---|
| `GoogleAnalyticsTool` | `app/Modules/Analytics/GoogleAnalytics/Tools/` | GA Data API lekérdezések (overview, top pages, compare period, realtime, ...) action discriminator-ral |

### 3. Context — DB-backed prompt-fragment

Az `AiContext` model 3 típusú prompt-darabot tárol (`AiContextType` enum):

| Típus | Mit jelent | Példa |
|---|---|---|
| `SYSTEM` | Az agent system promptja — szerep + viselkedési szabályok | `report-analyst-instructions`, `ga-analyst-instructions` |
| `PRESET` | A felhasználó által választható "riport típus" — ez kerül a prompt elejére | `traffic-overview`, `ga-conversion-funnel` |
| `INSTRUCTION` | Megosztott prompt-szakasz, ami több folyamhoz is hozzátehető | `output-format`, `heatmap-analysis` |

A context-eket **`ContextTag`** enummal címkézzük (`CLARITY` / `PAGE_ANALYSER` / `GA`), így a Livewire form leszűrheti pl. csak a GA-tagged preset-eket: `whereJsonContains('tags', ContextTag::GA->value)`.

**Tárolási minta**:
- Markdown fájl: `resources/ai-prompts/<slug>.md` (system + instruction) vagy `resources/ai-prompts/presets/<slug>.md` (preset)
- DB rekord: `database/seeders/AiContextSeeder.php` — `AiContext::updateOrCreate(['slug' => ...], [...])` minta, `context` mezőbe `file_get_contents(resource_path(...))`
- A felhasználó a `/settings/contexts` UI-on tudja kapcsolgatni, módosítani

### 4. Gateway — HTTP transport

A Laravel/AI library biztosítja az `OpenAiGateway`-t (és Anthropic, Gemini stb.). Vyzor egyetlen patch-et alkalmaz: **`TimeoutAwareOpenAiGateway`** (lásd *Active patches*).

A gateway-t az [`AiServiceProvider::boot()`](../app/Modules/Ai/AiServiceProvider.php) cseréli le `AiManager::extend('openai', ...)`-szel — minden OpenAI hívás a Vyzor-os subclass-on át megy.

### 5. Service — orchestrator

Az [`ReportGeneratorService`](../app/Modules/Reports/Services/ReportGeneratorService.php) a **kapocs** a Report rekord és az AI futás között. Egy futás során:

1. Beolvassa a riport `preset` slugját → tölti az `AiContext` row-t
2. **Flavor detektál**: `page` ha van `page_url`, `ga` ha a preset GA-tagged, különben `clarity`
3. Választ egy Agent-et a flavor szerint
4. Buildeli a promptot (`buildPagePrompt` / `buildGaPrompt` / `buildClarityPrompt`) — a flavor-specifikus adatokkal (HTML / GA snapshot / Clarity insights)
5. Hívja `agent->prompt($prompt)` → vár az AI válaszra
6. Üres választ FAIL-nek tekint (lásd `RuntimeException` az `generate()`-ben)
7. Frissíti a Report-ot: `content` + `status` (COMPLETED vagy FAILED)

A **flavor-selector** mintát követed, ha új riport-típust adsz hozzá — ne ágazz el a Job-ban, csak a service-ben.

---

## Hol él minden — fájl-térkép

```
app/Modules/Ai/
├── Agents/                                  # AI personality definitions
│   ├── PageAnalyst.php                      # URL-based analysis
│   └── ReportAnalyst.php                    # Analytics reports (Clarity + GA via instructionsSlug)
├── Contexts/                                # DB-backed prompt fragments
│   ├── Models/
│   │   ├── AiContext.php                    # the model
│   │   └── LLMContextPreset.php             # legacy, deprecated
│   └── Enums/
│       ├── AiContextType.php                # PRESET | SYSTEM | INSTRUCTION
│       └── ContextTag.php                   # CLARITY | PAGE_ANALYSER | GA
├── Tools/                                   # AI tool infrastructure
│   └── ProjectScopedTool.php                # base class for all Vyzor AI tools
├── Gateway/                                 # HTTP transport patches
│   └── TimeoutAwareOpenAiGateway.php        # bug fix for tool-call loop timeout
└── AiServiceProvider.php                    # registers gateway override

app/Modules/Analytics/GoogleAnalytics/Tools/
└── GoogleAnalyticsTool.php                  # function-calling tool (extends ProjectScopedTool)

app/Modules/Reports/
├── Models/Report.php                        # the Report record
├── Jobs/GenerateAiReport.php                # queue job (timeout=300, tries=1)
├── Services/
│   ├── ReportGeneratorService.php           # the orchestrator
│   └── HtmlFetcherService.php               # used by PageAnalyst flow
└── Enums/ReportStatusEnum.php               # PENDING | GENERATING | COMPLETED | FAILED

resources/ai-prompts/
├── report-analyst-instructions.md           # SYSTEM context source (Clarity)
├── ga-analyst-instructions.md               # SYSTEM context source (GA)
├── page-analyst-instructions.md             # SYSTEM context source (Page)
├── output-format.md                         # INSTRUCTION (shared)
├── heatmap-analysis.md                      # INSTRUCTION (Clarity heatmaps)
└── presets/                                 # PRESET sources
    ├── ga-*.md                              # GA-tagged presets
    ├── *clarity-related*.md                 # Clarity-tagged presets
    └── seo-audit.md, *.md                   # PAGE_ANALYSER-tagged presets

database/seeders/AiContextSeeder.php          # idempotent seeder for AiContext rows

resources/views/
├── pages/⚡{clarity-clarity,ga,clarity-page}-report.blade.php   # report request pages
└── components/⚡{clarity,ga}-report-tab.blade.php               # request forms
```

---

## Recipes — hogyan adj hozzá újat

### Új preset (riport sablon) hozzáadása

1. Írd meg a markdown sablont: `resources/ai-prompts/presets/my-new-preset.md`
2. Add hozzá a seeder-hez: `database/seeders/AiContextSeeder.php`
   ```php
   [
       'name'        => 'Az én új sablonom',
       'slug'        => 'my-new-preset',
       'type'        => AiContextType::PRESET,
       'models'      => ['all'],
       'tags'        => [ContextTag::GA->value],         // melyik flow-hoz tartozik
       'icon'        => 'chart-bar',                      // Phosphor icon név
       'label_color' => '#3b82f6',
       'description' => 'Mit ad ez a sablon, egy mondatban.',
       'sort_order'  => 25,
       'context'     => file_get_contents(resource_path('ai-prompts/presets/my-new-preset.md')),
   ],
   ```
3. `php artisan db:seed --class=AiContextSeeder`

A preset megjelenik annál a Livewire form-nál, ami az adott `ContextTag`-re szűr.

### Új AI tool hozzáadása (egy másik modul adatát exponálni az AI-nak)

1. Készíts egy osztályt a forrás modul `Tools/` mappájában (pl. `app/Modules/Clarity/Tools/ClarityTool.php`)
2. Extend-eld a `ProjectScopedTool`-t:
   ```php
   class ClarityTool extends ProjectScopedTool
   {
       public function __construct(
           Project $project,
           public readonly ClarityAggregator $aggregator,
       ) {
           parent::__construct($project);
       }

       public function description(): string { return 'Lekérdez Clarity adatokat a project-re...'; }

       public function schema(JsonSchema $schema): array
       {
           return [
               'metric' => $schema->string()->required()->description('...'),
               // FONTOS: minden array-en items(), minden opcionálison nullable() + required()
               // (OpenAI strict mode szabályai — lásd a Tool szakaszt)
           ];
       }

       protected function execute(Request $request): array
       {
           // Csináld a munkát; throw-olhatsz hibára, a base class JSON-né alakítja
           return $this->aggregator->doSomething($this->project, $request->string('metric'));
       }
   }
   ```
3. Az érintett Agent `tools()` metódusába tedd be:
   ```php
   public function tools(): iterable
   {
       $tools = [];
       if ($this->project?->hasClarityKey()) {
           $tools[] = new ClarityTool($this->project, app(ClarityAggregator::class));
       }
       return $tools;  // plain array, ne yield!
   }
   ```

### Új flavor + új Agent hozzáadása (új típusú riport)

Tegyük fel hozzá akarsz adni egy "Hotjar Insights" flavor-t.

1. **Új ContextTag**: `app/Modules/Ai/Contexts/Enums/ContextTag.php` → új `case HOTJAR = 'hotjar';`
2. **Új system context**: `resources/ai-prompts/hotjar-analyst-instructions.md` + seeder bejegyzés `tags: [ContextTag::HOTJAR->value]`
3. **Új preset(ek)** ugyanúgy taggelve
4. **Flavor felismerés**: `ReportGeneratorService::resolveFlavor()` kiegészül egy újabb `if (preset has HOTJAR tag) return 'hotjar'` ággal
5. **Agent választás**: `resolveAgent()` match-jébe `'hotjar' => new ReportAnalyst($project, instructionsSlug: 'hotjar-analyst-instructions')` ÉS/VAGY teljesen új agent osztály ha más tool-ok kellenek
6. **Prompt builder**: új `buildHotjarPrompt()` metódus, és a `buildPrompt()` match kapcsolja meg

A többi (Job, Livewire UI, permissions) mintára építhető a meglévő flow-kból.

### Új AI provider hozzáadása (pl. Anthropic Claude)

1. `config/ai.php`-be add hozzá az Anthropic providert (a Laravel/AI lib támogatja)
2. Ha kell timeout-fix az ő tool-call loopjához is: tükrözd a `TimeoutAwareOpenAiGateway` mintát egy `TimeoutAwareAnthropicGateway`-jel és `extend('anthropic', ...)`
3. Az agent `instructions()` és `tools()` változatlan — providerre nem érzékeny

---

## Konvenciók

### Naming
- **Agent**: `XAnalyst` minta (`ReportAnalyst`, `PageAnalyst`)
- **Tool**: `XTool` (`GoogleAnalyticsTool`)
- **Preset slug**: `kebab-case`, prefixed by tag-területtel (`ga-traffic-overview`)
- **System context slug**: `xy-analyst-instructions`
- **Markdown fájl helye**: `resources/ai-prompts/<slug>.md` (system / instruction) vagy `resources/ai-prompts/presets/<slug>.md` (preset)

### Permissions
A riport-flow két permissiont gateol:
- `VIEW_<MODUL>` — az oldalra való belépés (pl. `VIEW_GOOGLE_ANALYTICS`)
- `CREATE_REPORT` — a submit action

Mindkettőt **a page mount-ban + a Livewire submit action-ben is** ellenőrizni kell (defense-in-depth — a wire:click bypass-olhatja a mount-ot). Új permissionhoz:
1. Új `case` a `PermissionEnum`-ban
2. Hozzáadás a `WEB_PERMISSIONS` és `COLLABORATOR_PERMISSIONS` konstansokba a `PermissionSeeder`-ben
3. **Overseer automatikusan kapja** a `User::permissionsForRoles()` short-circuit-en át — nincs DB row, design szerint
4. `php artisan db:seed --class=PermissionSeeder`

### Translations
Minden felhasználó-felé szöveget `__('Angol forrás')` formában írj. A magyar fordítás a `lang/hu.json`-ba kerül. **Ne** írj keményen magyar szöveget a Livewire view-ba — a `__()` wrapper miatt később könnyű többnyelvűsíteni.

### Hibakezelés
- **AI üres válasz** → `ReportGeneratorService::generate()` `RuntimeException`-t dob, a catch FAILED-re állítja
- **Tool exception** → `ProjectScopedTool::handle()` automatikusan `{"error": "..."}` JSON-ná alakítja, az AI ezt látja és értelmezni tudja
- **Job timeout/szerver crash** → `GenerateAiReport::failed()` log + Report FAILED státusz

---

## Active patches / workarounds

### `TimeoutAwareOpenAiGateway`

**Mit kerül meg**: a `laravel/ai` library `ParsesTextResponses::continueWithToolResults()` metódusa nem továbbítja az agent `#[Timeout(N)]` értékét a tool-call follow-up POST-jaira. Ezért minden tool-using flow 60 másodperc után cURL-timeout-ot kapott, függetlenül a `Timeout(300)` attribute-tól.

**Hogyan**: subclass-oljuk az `OpenAiGateway`-t, befogjuk a timeout-ot egy `private ?int $currentTimeout`-ba a `generateText()` belépéskor, és felülírjuk a trait `client()` metódusát hogy ezt használja fallback-ként.

**Mikor lesz törölhető**: amikor a Laravel/AI library merge-eli az upstream PR-t — utána a `TimeoutAwareOpenAiGateway` és az `AiServiceProvider::boot()`-ban a `extend('openai', ...)` hívás eltávolítható. Részletek: [google-analytics-future-work.md](plans/google-analytics-future-work.md).

### `LLMContextPreset` legacy model

Még ott van a `Contexts/Models/`-ben de új kód NE használja. Az `AiContext` váltotta le. Egy jövőbeli migráció törölheti a tartalmazó táblát.

---

## Cheat sheet

**Riport flow per ágban (mit használj?)**:
- Page URL elemzés → `flavor='page'`, `PageAnalyst`, `buildPagePrompt`
- Clarity adatból riport → `flavor='clarity'`, `ReportAnalyst('report-analyst-instructions')`, `buildClarityPrompt`
- GA adatból riport → `flavor='ga'`, `ReportAnalyst('ga-analyst-instructions')`, `buildGaPrompt`

**Hogyan találod meg ki hívja meg a tool-t?**: keresd a `tools()` implementációkat — `grep -rn "function tools()" app/Modules/Ai/Agents`. Annak az agentnek a használati helyén látszik melyik flow-ban él.

**Új preset megjelenik a UI-on?**: ellenőrizd hogy a `tags`-be tetted a megfelelő `ContextTag`-et és hogy `is_active = true`. A Livewire form a `whereJsonContains('tags', ContextTag::X->value)` szűrővel csak a megfelelőket mutatja.

**Tool 400-as schema hiba OpenAI-tól**: 99%-ban az strict mode szabályok megsértése — minden property-t `required()`, opcionálisokat `nullable()`, array-eken `items()`, inner object-eken `withoutAdditionalProperties()`. Tinker-ből kidumpolhatod a generált schemát az `ObjectSchema`-n át.

**A `ReportGeneratorService` failed riport content-je hova kerül?**: a `Report.content` mezőbe `"Error: <üzenet>"` formában, és `Report.status = FAILED`. A `/reports/{id}` view ezt láthatóvá teszi a felhasználónak.

---

## További olvasnivaló

- **Modul-szintű architektúra**: [`docs/PROJECT_STRUCTURE.md`](PROJECT_STRUCTURE.md)
- **GA modul kód**: [`app/Modules/Analytics/GoogleAnalytics/`](../app/Modules/Analytics/GoogleAnalytics/)
- **GA jövőbeli munka + patch-listák**: [`docs/plans/google-analytics-future-work.md`](plans/google-analytics-future-work.md)
- **Laravel/AI library docs**: a `vendor/laravel/ai/` README és source — különösen `Promptable.php` és `Gateway/OpenAi/OpenAiGateway.php`
- **OpenAI strict-mode tool schema szabályok**: [platform.openai.com/docs/guides/structured-outputs](https://platform.openai.com/docs/guides/structured-outputs) (ugyanazok érvényesek tools-ra is)
