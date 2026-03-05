import type { AnalyticsProvider, ProviderId } from "./types";
import { ClarityProvider } from "./clarity";
import { ContentSquareProvider } from "./contentsquare";

const providerConstructors: Record<ProviderId, () => AnalyticsProvider> = {
  clarity: () => new ClarityProvider(),
  contentsquare: () => new ContentSquareProvider(),
};

const providerInstances = new Map<ProviderId, AnalyticsProvider>();

export async function getProvider(id: ProviderId): Promise<AnalyticsProvider> {
  if (!providerInstances.has(id)) {
    const constructor = providerConstructors[id];
    if (!constructor) throw new Error(`Unknown provider: ${id}`);
    const provider = constructor();
    await provider.initialize();
    providerInstances.set(id, provider);
  }
  return providerInstances.get(id)!;
}

export function getRegisteredProviderIds(): ProviderId[] {
  return Object.keys(providerConstructors) as ProviderId[];
}

export function getProviderMeta() {
  return Object.entries(providerConstructors).map(([, ctor]) => {
    const provider = ctor();
    return { ...provider.meta, capabilities: provider.capabilities };
  });
}
