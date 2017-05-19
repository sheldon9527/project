<?php
use Lokielse\LaravelPinyin\Pinyin as Pinyin;

class PinyinTest extends \PHPUnit_Framework_TestCase
{

    public function testConvert()
    {
        $pinyin = new Pinyin();
        $this->assertEquals('JinTianTianQiBuCuo', $pinyin->convert('今天天气不错'));
        $this->assertEquals('jintiantianqibucuo', $pinyin->convert('今天天气不错', Pinyin::POLICY_SHRINK));
        $this->assertEquals('JINTIANTIANQIBUCUO', $pinyin->convert('今天天气不错', Pinyin::POLICY_SHRINK, true));
        $this->assertEquals('jinTianTianQiBuCuo', $pinyin->convert('今天天气不错', Pinyin::POLICY_CAMEL));
        $this->assertEquals('JinTianTianQiBuCuo', $pinyin->convert('今天天气不错', Pinyin::POLICY_STUDLY));
        $this->assertEquals('jin_tian_tian_qi_bu_cuo', $pinyin->convert('今天天气不错', Pinyin::POLICY_UNDERSCORE));
        $this->assertEquals('jin tian tian qi bu cuo', $pinyin->convert('今天天气不错', Pinyin::POLICY_BLANK));
        $this->assertEquals('jin-tian-tian-qi-bu-cuo', $pinyin->convert('今天天气不错', Pinyin::POLICY_HYPHEN));
        $this->assertEquals('hong-mie-yu-mang-song', $pinyin->convert('叿吀吁吂吅', Pinyin::POLICY_HYPHEN));
        $this->assertEquals('he-ji-huai-chong-wei-che-xu', $pinyin->convert('喛喞喟喠喡喢喣', Pinyin::POLICY_HYPHEN));
        $this->assertEquals('english', $pinyin->convert('English', Pinyin::POLICY_HYPHEN));
    }

    public function testSetDefaultPolicy()
    {
        $pinyin = new Pinyin();
        $this->assertEquals('JinTianTianQiBuCuo', $pinyin->convert('今天天气不错'));
        Pinyin::setDefaultPolicy(Pinyin::POLICY_UNDERSCORE);
        $this->assertEquals('jin_tian_tian_qi_bu_cuo', $pinyin->convert('今天天气不错'));
        Pinyin::setDefaultUpperCase(true);
        $this->assertEquals('JIN_TIAN_TIAN_QI_BU_CUO', $pinyin->convert('今天天气不错'));
        Pinyin::setDefaultUpperCase(false);
        $this->assertEquals('jin_tian_tian_qi_bu_cuo', $pinyin->convert('今天天气不错'));
    }

    public function testFirst()
    {
        $pinyin = new Pinyin();
        $this->assertEquals('S', $pinyin->first('上海'));
        $this->assertEquals('C', $pinyin->first('China'));
    }

    public function testFirstEach()
    {
        $pinyin = new Pinyin();
        $this->assertEquals('SH', $pinyin->firstEach('上海'));
        $this->assertEquals('JDB', $pinyin->firstEach('加多宝'));
        $this->assertEquals('HSJ', $pinyin->firstEach('Hello世界'));
        $this->assertEquals('LOL', $pinyin->firstEach('league of legends'));
    }
}
