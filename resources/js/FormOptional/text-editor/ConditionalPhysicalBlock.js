import ConditionalBlockNode from './ConditionalBlock.js'

export default class ConditionalPhysicalBlockNode extends ConditionalBlockNode {

	get name() {
		return 'cblockphysical'
	}

	get conditionType() {
		return 'physical'
	}

	get tooltip() {
		return 'Show only for appointment at an address'
	}
}