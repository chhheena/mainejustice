import Quill from 'quill';
import EventBus from '../../eventbus';

const Embed = Quill.import('blots/embed');
class MentionBlot extends Embed {

  constructor(node) {
    super(node);

    node.addEventListener('click', (event) => {
      event.preventDefault();
      event.stopPropagation();

      EventBus.$emit('BlotClick', { event, node });
    });
  }

  static create(data) {
    const node = super.create();
    const span = document.createElement('span');

    span.className = 'mention';
    span.innerHTML = data.value;
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

MentionBlot.blotName = 'mention';
MentionBlot.tagName = 'span';
MentionBlot.className = 'quill-mention-item';

Quill.register(MentionBlot);
