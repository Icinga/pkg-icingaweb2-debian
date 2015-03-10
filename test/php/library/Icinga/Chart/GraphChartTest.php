<?php
/* Icinga Web 2 | (c) 2013-2015 Icinga Development Team | http://www.gnu.org/licenses/gpl-2.0.txt */

namespace Tests\Icinga\Chart;

use DOMXPath;
use DOMDocument;
use Icinga\Chart\GridChart;
use Icinga\Test\BaseTestCase;

class GraphChartTest extends BaseTestCase
{
    public function testBarChartCreation()
    {
        $chart = new GridChart();
        $chart->drawBars(
            array(
                'label' => 'My bar',
                'color' => 'black',
                'data'  => array(array(0, 0), array(1,1), array(1,2))
            )
        );
        $doc = new DOMDocument();
        $doc->preserveWhiteSpace = false;
        $doc->loadXML($chart->render());
        $xpath = new DOMXPath($doc);
        $xpath->registerNamespace('x', 'http://www.w3.org/2000/svg');
        $path = $xpath->query('//x:rect[@data-icinga-graph-type="bar"]');
        $this->assertEquals(6, $path->length, 'Assert the correct number of datapoints being drawn as SVG bars');
    }

    public function testLineChartCreation()
    {
        $chart = new GridChart();
        $chart->drawLines(
            array(
                'label' => 'My bar',
                'color' => 'black',
                'data'  => array(array(0, 0), array(1,1), array(1,2))
            )
        );
        $doc = new DOMDocument();
        $doc->preserveWhiteSpace = false;
        $doc->loadXML($chart->render());
        $xpath = new DOMXPath($doc);
        $xpath->registerNamespace('x', 'http://www.w3.org/2000/svg');
        $path = $xpath->query('//x:path[@data-icinga-graph-type="line"]');
        $this->assertEquals(1, $path->length, 'Assert the correct number of datapoints being drawn as SVG lines');
    }
}
