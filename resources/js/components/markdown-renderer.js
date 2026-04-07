import { marked } from "marked";
import hljs from "highlight.js";

marked.use({
    gfm: true,
    breaks: true,
    renderer: {
        code({ text, lang }) {
            const code =
                lang && hljs.getLanguage(lang)
                    ? hljs.highlight(text, { language: lang }).value
                    : text;
            return `<pre class="hljs"><code>${code}</code></pre>`;
        },
    },
});

Alpine.data("markdownRenderer", () => ({
    rendered: "",

    init() {
        this.render();

        // Re-render after Livewire updates (e.g. save/cancel editing)
        Livewire.hook("morph.updated", () => {
            this.$nextTick(() => this.render());
        });
    },

    render() {
        const source = this.$refs.source?.textContent || "";
        this.rendered = marked.parse(source);
    },
}));
