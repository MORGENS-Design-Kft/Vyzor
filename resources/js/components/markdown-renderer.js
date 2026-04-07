import markdownit from "markdown-it";
import hljs from "highlight.js";

const md = markdownit({
    html: false,
    linkify: true,
    typographer: true,
    highlight: function (str, lang) {
        if (lang && hljs.getLanguage(lang)) {
            try {
                return `<pre class="hljs"><code>${hljs.highlight(str, { language: lang }).value}</code></pre>`;
            } catch (_) {}
        }
        return `<pre class="hljs"><code>${md.utils.escapeHtml(str)}</code></pre>`;
    },
});

Alpine.data("markdownRenderer", () => ({
    rendered: "",

    init() {
        this.render();
    },

    render() {
        const source = this.$refs.source?.textContent || "";
        this.rendered = md.render(source);
    },
}));
