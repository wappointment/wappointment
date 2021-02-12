import ConditionalBlockNode from './ConditionalBlock.js'

export default class ConditionalZoomBlockNode extends ConditionalBlockNode {

	get name() {
		return 'cblockzoom'
	}

	get conditionType() {
		return 'zoom'
	}

	get tooltip() {
		return 'Shows when Video appointment'
	}
}