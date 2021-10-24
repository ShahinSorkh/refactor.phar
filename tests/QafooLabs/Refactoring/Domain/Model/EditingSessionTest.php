<?php
/**
 * Qafoo PHP Refactoring Browser
 *
 * LICENSE
 *
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.txt.
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to kontakt@beberlei.de so I can send you a copy immediately.
 */

namespace Tests\QafooLabs\Refactoring\Domain\Model;

use PHPUnit\Framework\TestCase;
use QafooLabs\Refactoring\Domain\Model\EditingSession;

class EditingSessionTest extends TestCase
{
    private $session;

    private $buffer;

    protected function setUp(): void
    {
        $this->buffer = $this->createMock('QafooLabs\Refactoring\Domain\Model\EditorBuffer');

        $this->session = new EditingSession($this->buffer);
    }

    public function testEditActionsArePerformed()
    {
        $action1 = $this->createMock('QafooLabs\Refactoring\Domain\Model\EditingAction');
        $action2 = $this->createMock('QafooLabs\Refactoring\Domain\Model\EditingAction');

        $action1->expects($this->once())
                ->method('performEdit')
                ->with($this->equalTo($this->buffer));

        $action2->expects($this->once())
                ->method('performEdit')
                ->with($this->equalTo($this->buffer));

        $this->session->addEdit($action1);
        $this->session->addEdit($action2);

        $this->session->performEdits();
    }
}
