import { Node } from 'tiptap'
import { toggleBlockType, toggleWrap} from 'tiptap-commands'

export default class ConditionalBlockNode extends Node {


	get schema() {
		return {
			content: 'block*',
			group: 'block',
			code: true,
			defining: true,
			draggable: false,
			parseDOM: [
				{ tag: 'p|conditional-'+ this.conditionType },
			],
			toDOM: () => ['p', { class: 'conditional conditional-' + this.conditionType, 'data-tt': this.tooltip}, 0],
		}
	}
/* 	toDOM: () => ['blockquote', 0],
		}
	}
 */
	commands({ type, schema }) {
		return () => toggleWrap(type, schema.nodes.paragraph)
	}
/* 	command({ type, schema, attrs }) {
		return toggleBlockType(type, schema.nodes.paragraph, attrs)
	} */

}