<?php
/* Icinga Web 2 | (c) 2013-2015 Icinga Development Team | http://www.gnu.org/licenses/gpl-2.0.txt */

namespace Tests\Icinga\File;

use Mockery;
use Icinga\File\Csv;
use Icinga\Test\BaseTestCase;

class CsvTest extends BaseTestCase
{
    public function testWhetherValidCsvIsRendered()
    {
        $queryMock = Mockery::mock(
            'Icinga\Data\SimpleQuery',
            array(
                'getQuery->fetchAll' => array(
                    array('col1' => 'val1', 'col2' => 'val2', 'col3' => 'val3', 'col4' => 'val4'),
                    array('col1' => 'val5', 'col2' => 'val6', 'col3' => 'val7', 'col4' => 'val8')
                )
            )
        );
        $csv = Csv::fromQuery($queryMock);

        $this->assertEquals(
            join(
                "\r\n",
                array(
                    'col1,col2,col3,col4',
                    '"val1","val2","val3","val4"',
                    '"val5","val6","val7","val8"'
                )
            ) . "\r\n",
            (string) $csv,
            'Csv does not render valid/correct csv structured data'
        );
    }
}
