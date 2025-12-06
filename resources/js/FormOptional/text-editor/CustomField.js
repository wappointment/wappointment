import { Node } from 'tiptap'
import TEVueIframe from './customfield.vue'

export default class CustomFieldNode extends Node {

	get name() {
		return 'customfield'
	}

	get schema() {
		return {
			inline: true,
			content: 'text*',
			attrs: {
				src: {},
				alt: {
					default: null,
				},
				class:{
					default: 'customfield',
				},
				title: {
					default: null,
				},
			},
			group: 'inline',
			draggable: true,
			selectable: true,
			parseDOM: [
				{
					tag: 'span[class=customfield]',
					getAttrs: dom => ({
						src: dom.getAttribute('src'),
						title: dom.getAttribute('title'),
						alt: dom.getAttribute('alt'),
					}),
				},
			],
			toDOM: node => ['span', node.attrs],
		}
	}

	commands({ type }) {
		return attrs => (state, dispatch) => {
			const { selection } = state
			const position = selection.$cursor ? selection.$cursor.pos : selection.$to.pos
			const node = type.create(attrs)
			const transaction = state.tr.insert(position, node)
			dispatch(transaction)
		}
	}

    
    get view() {
        return TEVueIframe
    }

}
