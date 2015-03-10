<?php
/* Icinga Web 2 | (c) 2013-2015 Icinga Development Team | http://www.gnu.org/licenses/gpl-2.0.txt */

namespace Tests\Icinga\Util;

use Icinga\Util\File;
use Icinga\Test\BaseTestCase;

class FileTest extends BaseTestCase
{
    /**
     * @expectedException \Icinga\Exception\NotWritableError
     */
    public function testWhetherWritingToNonWritableFilesThrowsAnException()
    {
        $file = new File('/dev/null');
        $file->fwrite('test');
    }

    /**
     * @expectedException \Icinga\Exception\NotWritableError
     */
    public function testWhetherTruncatingNonWritableFilesThrowsAnException()
    {
        $file = new File('/dev/null');
        $file->ftruncate(0);
    }
}
