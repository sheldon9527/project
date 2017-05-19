Laravel-Pinyin
==========

## Installation

The Pinyin service provider can be installed via [composer](http://getcomposer.org) by requiring the `lokielse/laravel-pinyin` package in your project's `composer.json`.

```json
{
    "require": {
        "lokielse/laravel-pinyin": "dev-master"
    }
}
```

Next, add the service provider to `app/config/app.php`.

```php
'providers' => [
    //..
    'Lokielse\LaravelPinyin\LaravelPinyinServiceProvider',
]
```

Then, add alias
```php
'aliases' => [
    //..
    'Pinyin'=>'Lokielse\LaravelPinyin\Facades\LaravelPinyinFacade',
]

```

That's it! You're good to go.

## Usages
```php
Pinyin::convert('今天天气不错');
# JinTianTianQiBuCuo

Pinyin::convert('今天天气不错', Pinyin::POLICY_SHRINK);
# jintiantianqibucuo

Pinyin::convert('今天天气不错', Pinyin::POLICY_SHRINK, true);
# JINTIANTIANQIBUCUO

Pinyin::convert('今天天气不错', Pinyin::POLICY_CAMEL);
# jinTianTianQiBuCuo

Pinyin::convert('今天天气不错', Pinyin::POLICY_STUDLY);
# JinTianTianQiBuCuo

Pinyin::convert('今天天气不错', Pinyin::POLICY_UNDERSCORE);
# jin_tian_tian_qi_bu_cuo

Pinyin::convert('今天天气不错', Pinyin::POLICY_BLANK);
# jin tian tian qi bu cuo

Pinyin::convert('今天天气不错', Pinyin::POLICY_HYPHEN);
# jin-tian-tian-qi-bu-cuo

Pinyin::convert('叿吀吁吂吅', Pinyin::POLICY_HYPHEN);
# hong-mie-yu-mang-song

Pinyin::convert('喛喞喟喠喡喢喣', Pinyin::POLICY_HYPHEN);
# he-ji-huai-chong-wei-che-xu

# first pinyin
Pinyin::first('上海市');
# S

Pinyin::first('China');
# C

# first each pinyin
Pinyin::firstEach('上海');
# SH

Pinyin::firstEach('加多宝');
# JDB

Pinyin::firstEach('league of legends');
# LOL

//default
Pinyin::setDefaultPolicy(Pinyin::POLICY_UNDERSCORE);
Pinyin::setDefaultUpperCase(true);

//PHP>=5.4 内置的方式
//@doc http://php.net/manual/en/transliterator.transliterate.php


transliterator_transliterate('Any-Latin', '凉茶我喝加多宝！还是原来的配方，还是熟悉的味道! I love JDB!')
#liáng chá wǒ hē jiā duō bǎo！ hái shì yuán lái de pèi fāng， hái shì shú xī de wèi dào! I love JDB!

transliterator_transliterate('Any-Latin;Latin-ASCII;lower()', '凉茶我喝加多宝！还是原来的配方，还是熟悉的味道! I love JDB!')
#liang cha wo he jia duo bao! hai shi yuan lai de pei fang, hai shi shu xi de wei dao! i love jdb!

transliterator_transliterate('Any-Latin;Latin-ASCII;upper()', '凉茶我喝加多宝！还是原来的配方，还是熟悉的味道! I love JDB!')
#LIANG CHA WO HE JIA DUO BAO! HAI SHI YUAN LAI DE PEI FANG, HAI SHI SHU XI DE WEI DAO! I LOVE JDB!


```

