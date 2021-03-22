import ConditionalBlockNode from './ConditionalBlock.js'

export default class ConditionalSkypeBlockNode extends ConditionalBlockNode {

	get name() {
		return 'cblockskype'
	}

	get conditionType() {
		return 'skype'
	}

	get tooltip() {
		return 'Shows when Skype appointment'
	}
}