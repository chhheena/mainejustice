import EventBus from '../eventbus';
import Quill from 'quill';

const Embed = Quill.import("blots/embed");
class EmojiBlot extends Embed {

    constructor(node) {
        super(node);

        node.addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();

            EventBus.$emit('BlotClick', {event, node});
        });
    }

    static create(data) {
        const node = super.create();
        const style = JSON.parse(data.style);
        const span = document.createElement('span');

        for (const key in style) {
            if (key === 'width' || key === 'height') {
                continue;
            }

            span.style[key] = style[key];
        }

        span.style.color = 'transparent';

        span.className = 'quill-emoji-native'
        span.innerHTML = data.native;
        node.appendChild(span);

        Object.keys(data).forEach((key) => {
            node.dataset[key] = data[key];
        });

        return node;
    }

    static value(domNode) {
        return domNode.dataset;
    }
}

EmojiBlot.blotName = 'emoji';
EmojiBlot.tagName = 'span';
EmojiBlot.className = 'quill-emoji';

Quill.register(EmojiBlot);
