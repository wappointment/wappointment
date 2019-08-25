<?php
if (!defined('ABSPATH')) {
    exit;
}
WappoSwift_DependencyContainer::getInstance()
    ->register('message.message')
    ->asNewInstanceOf('WappoSwift_Message')

    ->register('message.mimepart')
    ->asNewInstanceOf('WappoSwift_MimePart');
