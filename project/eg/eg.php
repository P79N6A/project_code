<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/19
 * Time: 18:37
 */
//use PHPUnit\Framework\TestCase;
#declare(strict_types=0);
abstract class AbstractFactory
{
    abstract public function createText(string $content):Text;
}

class JsonFactory extends AbstractFactory
{
    public function createText(string $content): Text
    {
        return new JsonText($content);
    }
}

class HtmlFactory extends AbstractFactory
{
    public function createText(string $content): Text
    {
        return new HtmlText($content);
    }
}

abstract class Text
{
    private $text;
    public function __constrict(string $text)
    {
        $this->text = $text;
    }
}
class JsonText extends Text
{

}
class HtmlText extends Text
{

}

//class AbstractFactoryTest extends TestCase
//{
//    public function testCanCreateHtmlText()
//    {
//        $factory = new HtmlFactory();
//        $text = $factory->createText('foobar');
//
//        $this->assertInstanceOf(HtmlText::class, $text);
//    }
//
//    public function testCanCreateJsonText()
//    {
//        $factory = new JsonFactory();
//        $text = $factory->createText('foobar');
//
//        $this->assertInstanceOf(JsonText::class, $text);
//    }
//}


function sumOfInts(int ...$ints)
{
    return array_sum($ints);
}
var_dump(sumOfInts(2,3,4,5,6));
