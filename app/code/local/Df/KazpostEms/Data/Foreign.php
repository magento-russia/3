<?php
namespace Df\KazpostEms\Data;
class Foreign {
	/** @var array(string => int) */
	public static $countries = array(
		'АВСТРИЯ' => 1
		,'АВСТРАЛИЯ' => 4
		,'АЗЕРБАЙДЖАН' => 0
		,'АЛБАНИЯ' => 1
		,'АЛЖИР' => 4
		,'АНГОЛА' => 4
		,'АНТИЛЬСКИЕ ОСТРОВА' => 5
		,'АРГЕНТИНА' => 4
		,'АРМЕНИЯ' => 0
		,'АРУБА' => 4
		,'АМЕРИКАНСКИЕ САМАО' => 5
		,'АНДОРРА' => 5
		,'АНГИЛЬЯ' => 5
		,'АНТИГУА' => 5
		,'АФГАНИСТАН' => 5
		,'БАГАМЫ' => 4
		,'БАНГЛАДЕШ' => 3
		,'БЕЛОРУССИЯ' => 0
		,'БЕЛЬГИЯ' => 1
		,'БОЛГАРИЯ' => 1
		,'БОТСВАНА' => 4
		,'БРАЗИЛИЯ' => 4
		,'БУТАН' => 3
		,'БАРБАДОС' => 5
		,'БАРБУДА' => 5
		,'БАХРЕЙН' => 2
		,'БЕЛИЗ' => 5
		,'БЕНИН' => 4
		,'БЕРЕГ СЛОНОВОЙ КОСТИ' => 5
		,'БЕРМУДСКИЕ О-ВА' => 5
		,'БОЛИВИЯ' => 4
		,'БОНАЙРЕ' => 5
		,'БОСНИЯ' => 1
		,'БРУНЕЙ' => 4
		,'БУРКИНА-ФАСО' => 4
		,'БУРУНДИ' => 4
		,'ВЕЛИКОБРИТАНИЯ' => 1
		,'ВЕНГРИЯ' => 1
		,'ВЕНЕСУЭЛА' => 4
		,'ВЬЕТНАМ' => 3
		,'ВАНУАТУ' => 5
		,'ВАТИКАН' => 1
		,'ВИРГИНСКИЕ О-ВА США' => 5
		,'ВИРГИНСКИЕ О-ВА БРИТ' => 5
		,'ГАБОН' => 5
		,'ГАЙАНА' => 5
		,'ГАНА' => 4
		,'ГЕРМАНИЯ' => 1
		,'ГИБРАЛТАР' => 5
		,'ГОНКОНГ' => 3
		,'ГРЕЦИЯ' => 1
		,'ГРУЗИЯ' => 0
		,'ГАИТИ' => 5
		,'ГАМБИЯ' => 5
		,'ГВАДЕЛУПА' => 5
		,'ГВАТЕМАЛА' => 5
		,'ГВИНЕЯ' => 5
		,'ГВИНЕЯ БИССАУ' => 5
		,'ГОНДУРАС' => 5
		,'ГРЕНАДА' => 5
		,'ГРЕНЛАНДИЯ' => 5
		,'ГУАМ' => 5
		,'ДАНИЯ' => 1
		,'ДЖИБУТИ' => 4
		,'ДОМИНИКА' => 5
		,'ДОМИНИКАНСКАЯ Р-КА' => 5
		,'ЕГИПЕТ' => 4
		,'ЗИМБАБВЕ' => 4
		,'ЗАМБИЯ' => 4
		,'ИЗРАИЛЬ' => 2
		,'ИНДИЯ' => 3
		,'ИНДОНЕЗИЯ' => 4
		,'ИОРДАНИЯ' => 2
		,'ИРАН' => 2
		,'ИРЛАНДИЯ' => 1
		,'ИСЛАНДИЯ' => 5
		,'ИСПАНИЯ' => 1
		,'ИТАЛИЯ' => 1
		,'ЙЕМЕН' => 2
		,'ИРАК' => 5
		,'КАМЕРУН' => 4
		,'КАНАДА' => 3
		,'КЕНИЯ' => 4
		,'КИПР' => 1
		,'КИТАЙ' => 2
		,'КОЛУМБИЯ' => 4
		,'КУВЕЙТ' => 2
		,'КЫРГЫЗСТАН' => 0
		,'КАЙМАНСКИЕ О-ВА' => 5
		,'КАМБОДЖА' => 3
		,'КОНГО' => 4
		,'КОНГО ДЕМ. Р-КА' => 4
		,'КОСТА-РИКА' => 5
		,'КУК О-ВА' => 5
		,'КЮРАСАО' => 5
		,'КАБО ВЕРДЕ' => 5
		,'КАТАР' => 2
		,'ЛАОС' => 3
		,'ЛАТВИЯ' => 1
		,'ЛИТВА' => 1
		,'ЛЮКСЕМБУРГ' => 5
		,'ЛЕСОТО' => 5
		,'ЛИБЕРИЯ' => 4
		,'ЛИВАН' => 2
		,'ЛИХТЕНШТЕЙН' => 5
		,'МАВРИТАНИЯ' => 5
		,'МАКАО' => 3
		,'МАКЕДОНИЯ' => 1
		,'МАЛАЗИЯ' => 4
		,'МАЛЬДИВЫ' => 4
		,'МАЛЬТА' => 1
		,'МАРОККО' => 4
		,'МЕКСИКА' => 4
		,'МОЛДОВА' => 0
		,'МОНГОЛИЯ' => 2
		,'МАВРИКИЙ' => 5
		,'МАДАГАСКАР' => 5
		,'МАЛАВИ' => 4
		,'МАЛИ' => 5
		,'МАЛЬДИВСКАЯ Р-КА' => 5
		,'МАРТИНИКА' => 5
		,'МАРШАЛОВЫ О-ВА' => 5
		,'МЬЯАНМАР (БИРМА)' => 3
		,'МИКРОНЕЗИЯ' => 5
		,'МОЗАМБИК' => 5
		,'МОНАКО' => 5
		,'МОНТСЕРРАТ' => 5
		,'НИГЕРИЯ' => 4
		,'НИДЕРЛАНДЫ' => 1
		,'НОВАЯ ЗЕЛАНДИЯ' => 4
		,'НОРВЕГИЯ' => 1
		,'НИДЕРЛАНДЫ АНТ. О-ВА' => 5
		,'НИКАРАГУА' => 5
		,'НОВАЯ КАЛЕДОНИЯ' => 5
		,'НАМИБИЯ' => 4
		,'НЕПАЛ' => 2
		,'НИГЕР' => 5
		,'ОАЭ' => 2
		,'ОМАН' => 2
		,'ПАКИСТАН' => 2
		,'ПАНАМА' => 4
		,'ПАРАГВАЙ' => 4
		,'ПЕРУ' => 4
		,'ПОЛЬША' => 1
		,'ПОРТУГАЛИЯ' => 1
		,'ПАЛАУ' => 5
		,'ПАЛЕСТИНА' => 5
		,'ПАПУА НОВАЯ ГВИНЕЯ' => 5
		,'ПУЭРТО-РИКО' => 5
		,'РОССИЯ (МОСКВА, САНКТ-ПЕТЕРБУРГ)' => 0
		,'РОССИЯ' => 0
		,'РУМЫНИЯ' => 1
		,'РУАНДА' => 4
		,'РЕЮНЬОН О-ВА' => 5
		,'САЛЬВАДОР' => 4
		,'СЕНЕГАЛ' => 5
		,'СИНГАПУР' => 4
		,'СИРИЯ' => 2
		,'СЛОВАКИЯ' => 1
		,'СЛОВЕНИЯ' => 1
		,'СУДАН' => 4
		,'США' => 3
		,'СЬЕРРА-ЛЕОНЕ' => 5
		,'САБА' => 5
		,'САЙПАН' => 5
		,'САНТ. БАРТОЛОМИ' => 5
		,'САНТ. ВИНСЕНТ' => 5
		,'САНТ. КИТС' => 5
		,'САНТ. ЛЮЧИЯ' => 5
		,'САНТ. МАРТИН' => 5
		,'САНТ. ЮСТАС' => 5
		,'САУДОВСКАЯ АРАВИЯ' => 2
		,'СВАЗИЛЕНД' => 5
		,'СЕЙШЕЛЬСКИЕ О-ВА' => 4
		,'СУРИНАМ' => 5
		,'ТАЙВАНЬ' => 5
		,'ТАЙЛАНД' => 3
		,'ТАНЗАНИЯ' => 4
		,'ТУНИС' => 4
		,'ТУРКМЕНИСТАН' => 0
		,'ТУРЦИЯ' => 1
		,'ТУРК И КАЙКОС О-ВА' => 5
		,'ТОГО' => 5
		,'ТРИНИДАД И ТОБАГО' => 5
		,'ТАДЖИКИСТАН' => 0
		,'УЗБЕКИСТАН' => 0
		,'УКРАИНА' => 0
		,'УГАНДА' => 4
		,'УРУГВАЙ' => 5
		,'ФИДЖИ' => 5
		,'ФИНЛЯНДИЯ' => 1
		,'ФРАНЦИЯ' => 1
		,'ФИЛИППИНЫ' => 4
		,'ФРАНЦУЗСКАЯ ГАЙАНА' => 5
		,'ФРАНЦУЗСКАЯ ПОЛИНЕЗИЯ' => 4
		,'ХОРВАТИЯ' => 1
		,'ЦАР' => 4
		,'ЧЕХИЯ' => 1
		,'ЧИЛИ' => 4
		,'ЧАД' => 5
		,'ШВЕЙЦАРИЯ' => 1
		,'ШВЕЦИЯ' => 1
		,'ШРИ-ЛАНКА' => 5
		,'ЭКВАДОР' => 4
		,'ЭСТОНИЯ' => 1
		,'ЭФИОПИЯ' => 4
		,'ЭКВАТОРИАЛЬНАЯ ГВИНЕЯ' => 5
		,'ЭРИТРЕЯ' => 4
		,'ЮАР' => 4
		,'ЮГОСЛАВИЯ' => 1
		,'ЮЖНАЯ КОРЕЯ' => 3
		,'ЯПОНИЯ' => 3
		,'ЯМАЙКА' => 4
	);

	/** @var array(int => int[]) */
	public static $_rates = array(
		150 => array(4230, 4180, 5230, 6280, 7330, 12280)
		,300 => array(4430, 4680, 5980, 7280, 8580, 13780)
		,500 => array(4730, 5180, 6730, 8280, 9830, 15280)
		,1000 => array(5130, 6180, 8230, 10280, 12330, 18280)
		,1500 => array(5630, 7180, 9730, 12280, 14830, 21280)
		,2000 => array(5930, 8180, 11230, 14280, 17330, 24280)
		,2500 => array(6130, 9180, 12730, 16280, 19830, 27280)
		,3000 => array(6330, 10180, 14230, 18280, 22330, 30280)
		,3500 => array(6630, 11180, 15730, 20280, 24830, 33280)
		,4000 => array(7130, 12180, 17230, 22280, 27330, 36280)
		,4500 => array(7630, 13180, 18730, 24280, 29830, 39280)
		,5000 => array(8130, 14180, 20230, 26280, 32330, 42280)
		,5500 => array(8630, 15180, 21730, 28280, 34830, 45280)
		,6000 => array(9130, 16180, 23230, 30280, 37330, 48280)
		,6500 => array(9630, 17180, 24730, 32280, 39830, 51280)
		,7000 => array(10130, 18180, 26230, 34280, 42330, 54280)
		,7500 => array(10630, 19180, 27730, 36280, 44830, 57280)
		,8000 => array(11130, 20180, 29230, 38280, 47330, 60280)
		,8500 => array(11630, 21180, 30730, 40280, 49830, 63280)
		,9000 => array(12130, 22180, 32230, 42280, 52330, 66280)
		,9500 => array(12630, 23180, 33730, 44280, 54830, 69280)
		,10000 => array(13130, 24180, 35230, 46280, 57330, 72280)
	);

	/** @var int[] */
	public static $_ratesMore = array(650, 1000, 1500, 2000, 2500, 3000);
}