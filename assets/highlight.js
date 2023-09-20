import php from 'highlight.js/lib/languages/php';
import twig from 'highlight.js/lib/languages/twig';
import hljs from "highlight.js";

hljs.registerLanguage('php', php);
hljs.registerLanguage('twig', twig);

hljs.initHighlightingOnLoad();
