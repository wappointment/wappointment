import ConditionalBlockNode from './ConditionalBlock.js'

export default class ConditionalPhoneBlockNode extends ConditionalBlockNode {

	get name() {
		return 'cblockphone'
	}

	get conditionType() {
		return 'phone'
	}

	get tooltip() {
		return 'Shows when phone appointment'
	}
}