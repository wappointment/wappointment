<?php
declare(strict_types=1);

namespace Wappointment\Controllers;

/**
 * Admin controller handling backend pages
 */
class AdminController extends BaseController
{
    /**
     * First page action
     */
    public function page1(): void
    {
        $this->render('admin/page1', [
            'title' => 'Page 1',
            'message' => 'Hello World from Page 1!'
        ]);
    }

    /**
     * Second page action
     */
    public function page2(): void
    {
        $this->render('admin/page2', [
            'title' => 'Page 2',
            'message' => 'Hello World from Page 2!'
        ]);
    }

    /**
     * Third page action
     */
    public function page3(): void
    {
        $this->render('admin/page3', [
            'title' => 'Page 3',
            'message' => 'Hello World from Page 3!'
        ]);
    }
}
