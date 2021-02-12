import ConditionalBlockNode from './ConditionalBlock.js'

export default class ConditionalPhysicalBlockNode extends ConditionalBlockNode {

	get name() {
		return 'cblockphysical'
	}

	get conditionType() {
		return 'physical'
	}

	get tooltip() {
		return 'Shows when appointment is at an address'
	}
}