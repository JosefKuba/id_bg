<?php

namespace App\Services;

use Minicli\App;
use Minicli\ServiceInterface;

class FaithService implements ServiceInterface
{
    use Trait\SelectTrait;

    private $app;

    public $good_keywords = [
        "台灣福音工作全時間訓練",
        "臺灣福音工作全時間訓練",
        "召会",
        "召會",
        "主的恢復",
        "傳道人",

        "基督教",
        "台福基督教會",
        "台福教會",
        "門諾會",
        "聖教會",
        "芥菜種會",
        "芥菜種教會",
        "久美教會",
        "K.D.M",
        "雅比斯教會",
        "东门巴克礼纪念教会",
        "信义会",
        "路德会",
        "路德教会",
        "信義會",
        "聖公會",
        "長老會",
        "宣道會",
        "宣道会",
        "歸正宗",
        "衛理公會",
        "门诺会",
        "長老教會",
        "學園傳道會",
        "马来西亚基督教巴色会士古来堂",
        "吉隆坡歸正福音教會",
        "皇后圣光教会- 基督教长老会",
        "基督教銘恩堂大埔堂",
        "卫理公会",
        "玉山神學院",
        "基督教中華循理會",
        "浸信",
        "校園福音團契",
        "宣道会",
        "中台神學院",
        "中華福音神學院",
        "客家宣教神學院",
        "台灣宣教神學院",
        "台宣神學院",
        "台灣宣道神學院",
        "台南神學院",
        "台灣神學院",
        "聖光神學院",
        "马来西亚圣经神学院",
        "沙巴神學院",
        "新竹聖經學院",
        "國際歐華神學院",
        "拿撒勒人耶穌基督教會",
        "改革宗神學院",
        "中華信義神學院",
        "Malaysia Bible Seminary",
        "长老会",
        "China Reformed Theological Seminary",
        "循理會",
        "循理教會",
        "诗巫卫理神学院",
        "马来西亚圣经神学院",
        "建道神學院",
        "长老教会",
        "马来西亚神学院",
        "台灣浸會神學院",
        "新生教團教會",
        "重慶教會",
        "永康教會",
        "羅斯福路教會(係今之永生教會)",
        "基督教新加坡生命堂 Singapore Life Church",
        "Lutheran Church",
        "右昌教會學青牧區",
        "磐頂教會",
        "花蓮壽豐教會",
        "EFCLA 洛福教會",
        "景美福音堂",
        "士林真理堂",
        "淡水真理堂",
        "林口樂福真理堂",
        "雙和真理堂",
        "土城真理堂",
        "樂福「LOVE」牧區",
        "古來真理堂",
        "台東卑南比那斯基教會",
        "印尼歸正福音教會 - 中文堂",
        "唐崇榮牧師",
        "浸信宣道會南京教會",
        "浸宣南京教會",
        "万民青团",
        "基督教新加坡生命堂",
        "香港教会尖沙咀聚会所：倪柝声",
        "金門基督教宣教及文化培訓中心",
        "蒙恩教会",
        "台灣基督長老教會",
        "TKC尚青",
        "嘉義蘭潭教會",
        "雷虎教會",
        "屏原教會",
        "Methodist Church",
        "Chinese Methodist Church",
        "诗巫爱莲街福源堂",
        "吉隆坡归正福音教会",
        "吉隆坡归正福音教会",
        "唐崇荣国际布道团",
        "Methodist Theological School",
        "卫理公会砂拉越华人年议会",
        "Taiwan Graduate School of Theology",
        "台灣神學研究學院",
        "Sabah Annual Conference, The Methodist Church in Malaysia",
        "Alliance Bible Seminary",
        "methodist church in malaysia ",
        "Malaysia Theological Seminary",
        "Trinity Theological College, Singapore",
        "Singapore Bible College",
        "Methodit Theological Seminary",
        "Wesley Methodist Church",
        "Methodist Theological School, Sibu",
        "Lutheran Church in Malaysia",
        "S.C.A.C - Pastor",
        "卫理公会砂拉越华人年议会",
        "新竹聖經學院",
        "砂拉越卫理公会大专事工小组",
        "诗巫归正福音教会 IREC Sibu",
        "民都鲁归正福音团契 IREF Bintulu",
        "Zion Methodist Church （Sibu North District）",
        "Seminari Theoloji Malaysia - STM 马来西亚神学院",
        "沙巴神学院",
        "甲洞马路里宣恩堂",
        "文良港青年团契",
        "Malaysia Baptist Theological Seminary",
        "中華福音學院",
        "卫理神学院",
        "中華基督教會協和堂",
        "天恩堂少年团契",
        "新山和平教会",
        "美农布道所",
        "AIMST 大专团契 - AMCF",
        "救恩堂 Saving Grace Church Batu Pahat",
        "车水路福音堂",
        "基督教怡保以琳福音堂",
        "诗巫布律克新福源堂",
        "圣道基督教会Holyword Church Berhad",
        "台灣基督長老教會麥寮恩惠教會",
        "法拉盛聯合聖經教會",
        "古来真理门徒教会",
        "莿桐教會",
        "大西教會",
        "丹鳳教會",
        "民和教會",
        "高雄前金教會",
        "新屋教會",
        "新生教團新生教會",
        "愛加倍榮光堂",
        "屏東基督崇蘭教會",
        "旭海教會",
        "新香蘭教會",
        "振興教會",
        "于宏潔",
        "归正福音教会",
        "哥打丁宜伯大尼基督教会",
        "台灣基督長老教會麥寮恩惠教會",
        "台灣正道福音神學院",
        "正道福音神學院",
        "台灣正道教牧博士科",
        "屏東磐石教會",
        "S.C.A.C",
        "Trinity Theological College",
        "Malaysia Baptist Convention",
        "Southeastern Baptist Theological Seminary",
        "China Evangelical Seminary",
        "Sarawak Chinese Annual Conference",
        "Pioneer United Church",
        "香港伯特利教會",
        "香港伯特利教會慈光堂",
        "The Southern Baptist Theological Seminary",
        "高雄信愛教會",
        "板橋福音堂",
        // "感謝神",
        // "上帝",
        // "聖經",
        // "感謝主",
        // "宣教",
        // "主日",
        // "耶稣",
        // "天父",
        // "天主",
        // "加利利宣教中心",
        // "恩典365",
        // "大光教會",
        // "新山异象基督教会",
        // "感謝神",
        // "上帝",
        // "聖經",
        // "感謝主",
        // "宣教",
        // "主日",
        // "耶稣",
        // "天父",
        // "天主",
        // "加利利宣教中心",
        // "大光教會",
        // "church",
        // "Seminary",
        // "priest",
        "牧师",
        // "pastor",
        // "教会",
        "神学院",
        "师母",
        "牧師",
        // "教會",
        "神學院",
        "師母",
        "Pastor",
        // "Church",
        // "布道",
        // "佈道",
        // "崇拜",
        // "传道",
        // "传道会",
        // "传教",
        // "傳道",
        // "傳道會",
        // "傳教",
        // "祷告",
        // "禱告",
        // "东教堂",
        // "东正教",
        // "東教堂",
        // "東正教",
        // "恩泉",
        // "福音",
        // "复活节",
        // "復活節",
        // "管家",
        // "荒漠甘泉",
        // "基督",
        // "基督教",
        // "教会",
        // "教會",
        // "教区",
        // "教區",
        // "教士",
        // "教堂",
        "浸信会",
        "浸信會",
        // "经文",
        // "經文",
        // "敬拜",
        // "救恩",
        // "救世",
        // "救赎",
        // "救贖",
        // "礼拜",
        // "禮拜",
        // "炼狱",
        // "煉獄",
        // "路德会",
        // "路德會",
        // "玛丽",
        // "玛利",
        // "瑪利",
        // "瑪莉",
        // "蒙恩",
        // "弥撒",
        // "彌撒",
        // "牧区",
        // "牧區",
        // "牧师",
        // "牧師",
        // "牧养",
        // "牧養",
        // "牧者",
        // "仆人",
        // "僕人",
        // "祈祷",
        // "祈禱",
        // "神父",
        // "神学",
        // "神学生",
        // "神学院",
        // "神学院学生",
        // "神學",
        // "神學院",
        // "神學院學生",
        // "生命灵粮",
        // "生命靈糧",
        // "生命之泉",
        // "圣彼得",
        // "圣公会",
        // "圣经",
        // "圣经学",
        // "圣灵",
        // "圣母",
        // "圣约翰",
        // "圣职者",
        // "聖保羅",
        // "聖彼得",
        // "聖公會",
        // "聖經",
        // "聖經學",
        // "聖靈",
        // "聖母",
        // "聖約翰",
        // "聖職者",
        // "诗篇",
        // "詩篇",
        // "十诫",
        // "十誡",
        // "十字架",
        // "使徒",
        // "事工",
        // "水深之处",
        // "水深之處",
        // "天恩",
        // "天主",
        // "同工",
        // "团契",
        // "團契",
        // "卫理公会",
        // "衛理公會",
        // "信任爱",
        // "信任愛",
        // "信望爱",
        // "信望愛",
        // "信义会",
        // "信義會",
        // "修女",
        // "宣道",
        // "宣教",
        // "耶和华",
        // "耶和華",
        // "耶路撒冷",
        // "耶稣",
        // "耶穌",
        // "以马内利",
        // "以馬內利",
        // "长老",
        // "长老会",
        // "长老教会",
        // "長老",
        // "長老教會",
        // "职事",
        // "職事",
        // "baptist",
        // "Christianity",
        // "church",
        // "Clergy",
        // "elder",
        // "Jesus",
        // "Minister",
        // "Mission",
        // "Missionary Society",
        // "Pastor",
        // "pastoral area",
        // "preach",
        // "Seminary",
        // "Shepherd",
        // "Slave",
        // "theological student",
    ];

    public $eval_keywords = [
        // 
        "大学",
        "殿",
        "医院",
        "醫院",
        // "台灣福音工作全時間訓練",
        // "召会",
        // "召會",
        "基督城",
        "小學",
        "中學",
        "大學",
        "學校",
        "關渡基督書院",

        "摩门",
        "耶和华见证人",
        "真耶稣",
        "爱修园",
        "新生命教会",
        "安提阿教会",
        "三⾃教会",
        "美仑浸信会",
        "丰收教会",
        "灵恩派",
        "神召会",
        "贵格会",
        "得救派",
        "安息日会",
        "耶稣基督后期圣徒",
        "安商洪",
        "华雪和",
        "三赎",
        "新店行道会",
        "旌旗教会",
        "真葡萄树基督教会",
        "新生命小组",
        "玫琳凯",
        "全能教会",
        "旷野遇到神",
        "新天地教会",
        "复临安息日教会",
        "香港锡安教会",
        "救世军",
        "四方福音教会",
        "摩門",
        "耶和華見證人",
        "靈糧堂",
        "真耶穌",
        "愛修園",
        "新生命教會",
        "安提阿",
        "三⾃教會",
        "花蓮美崙浸信會",
        "豐收教會",
        "靈恩派",
        "神召會",
        "貴格會",
        "得救派",
        "安息日會",
        "耶穌基督後期聖徒",
        "安商洪",
        "華雪和",
        "三贖",
        "新店行道會",
        "旌旗教會",
        "真葡萄樹基督教會",
        "新生命小組",
        "玫琳凱",
        "全能教會",
        "曠野遇見神",
        "新天地教会",
        "復臨安息日教會",
        "香港錫安教會",
        "救世軍",
        "四方福音教會",
        "灵粮堂",
        "靈糧堂",
        "靈恩",
        "灵恩",
        "喜信会",
        "喜信教会",
        "福气教会",
        "真道教会",
        "韩国汝矣岛纯福音教会",
        "以斯拉事奉中心",
        "新生命小组教会",
        "喜信會",
        "喜信教會",
        "福氣教會",
        "真道教會",
        "韓國汝矣島純福音教會",
        "以斯拉事奉中心",
        "新生命小組教會",
        "圣乐教会",
        "超自然的能力",
        "先知特会",
        "黄国伦",
        "朴国华",
        "Gary Heyes",
        "张汉业",
        "顾其芸",
        "江秀琴",
        "慕主先锋",
        "祝瑞莲",
        "琴与炉",
        "林明干",
        "苗栗新希望怀恩教会",
        "聖樂教會",
        "超自然的能力",
        "先知特會",
        "黃國倫",
        "樸國華",
        "Gary Heyes",
        "張漢業",
        "顧其芸",
        "江秀琴",
        "慕主先鋒",
        "祝瑞蓮",
        "琴與爐",
        "林明幹",
        "苗栗新希望懷恩教會",
        "以琳全备福音教会",
        "汝矣岛纯福音教会",
        "聖靈醫治恩膏特會",
        "先知预言",
        "醫病趕鬼",
        "台北纯福音教会",
        "三育基督學院",
        "熾火國度復興特會",
        "台北榮耀堂",
        "TJC",
        "大衛帳幕教會",
        "墨爾本約明基督教會",
        "梧棲敬拜教會",
        "台灣復興基督教會",
        "新莊恩典堂",
        "和撒那",
        "大衛敬拜會幕",
        "台湾全福会",
        "台灣全福會",
        "荣耀锡安",
        "榮耀錫安",
        "榮耀錫安國際事工",
        "荣耀锡安国际事工",
        "和撒那事奉中心",
        "桃園大溪榮美教會",
        "翻方言",
        "使徒信心会",
        "台北灵粮堂",
        "五旬节圣洁会",
        "基督權能福音教會",
        "新北基督權能福音領袖學院",
        "新莊迦南教會",
        "新創教會",
        "汐止錫安教會",
        "大衛帳幕教會",
        "影響力豐盛教會",
        "高雄榮耀教會",
        "崇信教會",
        "新竹榮光教會",
        "生命樹迦南教會",
        "頭份家庭教會",
        "神國翻轉教會",
        "全球基督徒禱告院",
        "台北佳音教會",
        "葛揚明",
        "新山葡萄园教会",
        "生命河基督教會第二教會",
        "马六甲加略山生命堂",
        "淡江教會",
        "溪水旁教会",
        "台南活水基督教會",
        "台灣鎮海基督教會",
        "高雄榮耀基督教會",
        "高雄大使命教會",
        "希望國際宣道事工協會",
        "希望國際宣道事工",
        "聖靈之夜 Holy Spirit Night",
        "台灣希望國際宣道事工協會",
        "台北复兴堂",
        "佳音神學院",
        "中華民國基督教佳音宣教協會",
        "更生团契台湾总会",
        "台湾更生团契",
        "信望爱少年学园",
        "臺灣新浪潮神學院",
        "愛修園國際領袖學院",
        "Peace Charis AG 平恩教会",
        "三育",
        "神召神學院",
        "行動教會",
        "熾火之光權能更新事工",
        "iM 行動教會",
        "愛修園國際領袖學院",
        "五旬節聯合會燈塔教會",
        "台北榮耀教會",
        "台北復興堂",
        "基督教晨曦會",
        "基督教晨曦門徒訓練學院",
        "財團法人基督教晨曦會",
        "翻方言",
        "高雄廿四小時敬拜禱告中心",
        "高禱屋之聲",
        "高禱屋",
        "榮耀會幕",
        "德生堂教會 & 華岡團契",
        "新生命复兴教会 - 蕉赖南区中文堂",
        "异象生命中心（奥克兰中文堂）",
        "台北基督之家",
        "生命河基督教會中心網站",
        "高雄牧鄰教會",
        "真光教會",
        "愛修更新會",
        "Salem Chapel Singapore",
        "新加坡復興堂",
        " Chinese Pentacostal Church",
        "Calvary Victory Centre - CVC 加略山得胜中心",
        "淡边加略山生命堂 TCLA",
        "三峽大磐石純福音教會",
        "財團法人基督教中央教會總部",
        "屬靈軍隊中央教會總部thotl",
        "基督教大湳禮拜堂",
        "國度領袖聖教會",
        "別是巴聖教會",
        "台灣信義會高雄後勁教會",
        "Assemblies of God",
        "台灣復興神的教會",
        "神的教會",
        "深坑活石教會(台灣台北)",
        "基督教大湳禮拜堂",
        "基督教聖召會頭城福音中心",
        "財團法人基督教台灣信義會後勁教會",
        "八打灵全备福音堂",
        "甲洞全备福音堂",
        "USJ全备福音堂",
        "文良港全备福音堂",
        "吉隆坡全备福音堂",
        "蕉赖全备福音堂",
        "双溪兰全备福音堂",
        "双溪毛糯全备福音堂",
        "文良港全备福音堂",
        "傳揚福音事工團隊教會",
        "新竹雲端教會",
        "行道会",
        "行道會",
        "火把教會",
        "好消息宣教会",
        "朴玉洙",
        "台南美好基督教會",
        "朴國華牧師",
        "摩門教",
        "同志運動",
        "基恩之家",
        "BMCC 基恩之家",
        "穆斯林",
        "酷愛宗旅",
        "吉隆坡丰盛教会",
        "The Grace Centre 蒙恩教会",
        "新天地耶稣教证据帐幕圣殿",
        "基督教牧愛會",
        "基督教新竹尖石聖恩喜樂會",
        "基督教聖恩喜樂教會",
        "墨尔本祷告祭坛学院",
        "圣雅各福群会",
        "Seventh-day Adventist Church ",
        "台北 101 教會",
        "孫揚光",
        "真光福音教會",
        "德生堂教會& 華岡團契Desheng Christian Church",
        "華岡團契",
        "牧師 李鴻忠",
        "高雄慕恩教會",
        "基督教神國復興宜蘭教會",
        "神國復興魚池教",
        "心火教会",
        "火從天降烈火特會",
        "新市生命泉基督教會",
        "The Church of Jesus Christ of Latter-day Saints ",
        "羊的門歌珊基督教会",
        "Sheepgate Goshen Christian Church 羊的門歌珊基督教会",
        "萬國敬拜與讚美．台灣分會",
        "台東城市生命泉教會",
        "葡萄園家",
        "基督教台南葡萄園家職場教會",
        "台南活水教会",
        "台南磐石基督教会",
        "生命河基督教會府中教會",
        "生命河基督教會",
        "約書亞青年冒險團",
        "基督教沐恩之家",
        "香港基督教更新會 The Hong Kong Christian Kun Sun Association Ltd",
        "The Hong Kong Christian Kun Sun Association Ltd",
        "主基督荣光教会",
        "榮光教會",
        "桃園權能福音教會",
        "愛修園國際領袖學院",
        "興起發光",
        "主恩典教會",
        "榮美生命樹教會",
        "棉竹榮美教會",
        "重慶榮美教會",
        "成都榮美教會",
        "生命樹國度教會（裝備中心）",
        "五旬節合一敬拜特會",
        "合一基督教會",
        "蘭陽懷恩教會",
        "克安通 Anton Cruz",
        "台南虹橋傳道會",
        "嘉義美好基督教會",
        "溝子口錫安堂教會",
        "G.M.I 城市福音教會",
        "萬民敬拜禱告中心 House of Worship and Prayer for All People",
        "顏金龍 牧師",
        "大光教會青年牧區",
        "蚵寮基督教會",
        "台北神愛教會",
        "中央教會屬靈耶和華軍隊",
        "基甸300福音事工協會",
        "救世軍春風少年之家",
        "靈糧生命培訓學院",
        "生命河旌旗敬拜禱告祭壇",
        "和撒那事奉中心",
        "台北TOD",
        "大衛會幕禱告中心 ",
        "灵粮教牧宣教神学院",
        "城市之光教会",
        "台湾房角石教会",
        "神的教会",
        "全球教會",
        "默想教會",
        "默想教會/咖啡",
        "奇异恩典神迹国际教会",
        "松慕強牧師",
        "莊育銘牧師",
        "榮撒卡使徒事工",
        "以利沙学校",
        "Church of Praise, Ipoh",
        "宜蘭基督之家",
        "幸福城市教會",
        "泰山幸福教會",
        "彰化 神的居所",
        "國度精兵使徒中心",
        "新榮耀堂",
        "國際復興教會",
        "純福音敎會",
        "腓立教會",
        "花蓮基督權能福音",
        "鶯歌懷恩教會",
        "榮光小組教會",
        "蘆洲大使命教會",
        "大溪懷恩教會",
        "恩典基督教會&敬拜中心",
        "芙蓉爱恩社区教会 AGAPE Community Church Seremban",
        "The Church of Jesus Christ of Latter-day Saints",
        "神的教會",
        "神恩复兴运动",
        "神恩復興運動",
        "上帝的教会世界福音宣教协会",
        "安商洪",
        "高雄真道神學院 ",
        "恩惠教會",
        "火種牧師",
        "True Jesus Church",
        "庄国和牧师",
        "基督恩典中心",
        "圣灵浸",
        "恩典国度之子 Charis Kingdom Kids",
        "Charis Christian Centre",
        "靈糧教牧神學院",
        "城市祈禱中心 Sunrise House of Prayer",
        "POWER HOUSE 復興信息之夜",
        "復興聚會 POWER HOUSE",
        "復興海上絲綢之路・築起海上敬拜禱告祭壇",
        "吉隆玻甲洞神召会救恩堂",
        "韓泰鉉牧師",
        "生命泉靈糧教會",
        "石啟璽牧師",
        "生命糧教會",
        "布永康",
        "基督传万邦",
        "墨爾本榮耀城教會 (Glory City Church)",
        "趙鏞基",
        "緬甸景棟教會",
        "砂拉越蒙福教会",
        "古晋蒙福教会",
        "新山加略山社区教会",
        "烏干達Dennis 先知",
        "中壢哈利路亞家教會",
        "中壢家教會",
        "哈利路亞家教會",
        "臺灣毫無隱藏事工",
        "台南葡萄園教會",
        "吉隆坡复兴教会",
        "Calvary City Church",
        "溝子口錫安堂",
        "五股禮拜堂",
        "復興教會真奥堂",
        "恩惠國際神學院",
        "吳柏彤牧師",
        "高雄基督權能福音領袖學院cpe",
        "耶利米先知遇見神蹟特會",
        "以色列之旅九水恩膏",
        "生命河旌旗事工 Life river worship flags",
        "台中伯特利教會",
        "生命河敬拜禱告築壇獻祭",
        "莊瑞志牧師",
        "台南腓立比教會",
        "主燈塔事工",
        "瑞豐教會",
        "陳光道牧師",
        "蔡子榆",
        "台北基督徒七張禮拜堂",
        "以利亚先知学校",
        "台南磐石基督教會 Rock of Christ Church",
        "恩泉權能恩膏特會",
        "五股福音堂",
        "基督教五股福音堂",
        "高雄榮美福音中心",
        "深坑喜乐教会",
        "台北”Up生命力”教會",
        "Martina 黃韻如",
        "張振華牧師",
        "駱世雄牧師",
        "劉進展牧師",
        "台南自由基督教會",
        "邻恩复兴教会（卓越）Neighbour Grace Assembly",
        "湖木教會",
        "約爾·歐斯汀、",
        "趙鏞基",
        "吴清贵牧师",
        "城市卓越教会",
        "大使命浸信會",
        "先知阿摩司",
        "New Era 青年特會",
        "GMI大溪榮美教會",
        "李協聰牧師",
        "Peniel Taiwan 台灣毘努伊勒會",
        "CRC 利河伯教會",
        "愛德華米勒牧師",
        "臺灣基督教領袖聯合會",
        "火焰基督教會",
        "基督得勝教會",
        "Apostle 29 使徒行動",
        "猶大敬拜帳幕",
        "基督復臨安息日好茶教會",
        "兴盛教会",
        "以利亞牧師（Elias Antonas）特會",
        "CPE 基督權能福音特會",
        "新莊神能教會",
        "夏凱納國度敬拜中心",
        "葡萄樹教會",
        "台灣房角石教會",
        "薰塔羅魔法學院 Stella Tarot Magic Healing",
        "印度童阿南德",
        "基督教佳美宣教團",
        "牟敦康牧師",
        "基督國度使命團",
        "劉竹村牧師",
        "淑惠小組",
        "赞美灯塔教会",
        "豐富教會",
        "Full Gospel Church JB 新山全备福音教会",
        "恆春四方豐收教會",
        "約書亞團隊基督四方豐收教會",
        "臺南愛火燃燒復興特會",
        "約珥基督教會",
        "台南愛火燃燒復興特會",
        "生命流出版社有限公司",
        "ACKN國度使徒中心網絡（the Apostolic Center of Kingdom Network）",
        "UP生命力教會",
        "新加坡圣方济亚西西堂",
        "祈祷堡垒神恩复兴团体",
        "新生命复兴教会",
        "iM Church 行動教會 - Inspiring Moment",
        "朱秋全牧師",
        "韓國汝夷島純福音教會",
        "恩寵教會 Hesed Church",
        "麻坡加略山生命堂 Muar Calvary Life Assembly A/G",
        "BCCM Penampang 巴色会兵南邦堂",
        "The Connection 卓越连结教会",
        "哥打丁宜加略山社区教会 CCCKT",
        "國際五旬宗大會",
        "刘彤牧师",
        "Agmc International kuala Lumpur, Malaysia 奇异恩典神迹国际教会 ",
        "葛米勒牧師",
        "榮頌團契 Glorious Praise Fellowship",
        "吉隆坡非拉铁非教会 Philadelphia Church Kuala Lumpur (PCKL)",
        "灯塔教会中文聚会 Lighthouse Evangelism Chinese Service",
        "生命樹國度裝備中心",
        "劉肇陽牧師",
        "生命樹國度教會",
        "道格·巴契勒牧师",
        "周神助",
        "i 61 教會",
        "i61為光教會",
        "Tabernacle of Worship, Seremban Malaysia",
        "316新人教會",
        "埔里思恩堂Light Up 亮點教會",
        "四方福音會大角咀堂 -",
        "姚國樑",
        "FGA Church Prai 北赖全备福音教会",
        "Dennis牧師",
        "四週啟示性禱告訓練課程",
        "FIGHT.K",
        "基督徒宜蘭禮拜堂 ILan Church",
        "石牌合一堂",
        "海山教會",
        "基督大使教會 Ambassador Assembly",
        "台北101教會",
        "花蓮聖靈復興特會",
        "嘉義活水教會",
        "丹麦者伦牧师",
        "柔佛州三清道教會",
        "The Hope",
        "教會迷因推廣中心",
        "張茂松",
        "臼井灵气 & 天使灵气 治疗师",
        "台南協傳使徒基督教會",
        "美崙浸信會",
        "生命發展教會 ·",
        "基督教台宣伯大尼教會",
        "財團法人台灣基督教蒙恩宣教中心 ",
        "財團法人台灣基督教蒙恩宣教中心 ",
        "CHC Prai 北赖城市丰收",
        "Misi Kabar Baik Indonesia ",
        "台東復興祈禱院",
        "威廉．威爾遜",
        "亞細亞聖徒訪韓聖會",
        "台東復興祈禱聖會",
        "純福音世界宣教會台灣總會",
        "豐盛恩寵教會",
        "生命河旌旗敬拜禱告中心",
        "比撒列旌旗工作坊 Worship Banners of Bezalel",
        "列國羽翼 寶座敬拜",
        "靈糧教牧宣教神學院",
        "Every Nation Church Penang",
        "伯大尼堂",
        "四方福音",
        "古晋希望教会华文堂",
        "实达加略山社区教会",
        "Full Gospel Church Ayer Tawar 爱大华全备福音教会",
        "上环18荣耀基督徒中心",
        "马来西亚四方福音会",
        "Damien Chua牧师",
        "寻找.文冬以马内利敬拜团 SEEK.Immanuel Chapel Bentong Praise and Worship Teamm",
        "HOPE JB 新山希望教会",
        "巴生全备福音堂",
        "无拉港全备福音堂",
        "赛城全备福音堂",
        "大港全备福音堂",
        "沙登全备福音堂",
        "君尊祭司圣经学院",
        "FGA Rawang",
        "好牧人之家",
        "The Abundant Community Centre",
        "Calvary Community Church Johor Bahru",
        "Calvary City Church Tawau",
        "雅阁花园加略山社区教会 Taman Tan Sri Yaacob",
        "更新基督教会 Renewal Christian Church (Singapore) (更新圣乔治)",
        "The Good Shepherd Church",
        "古晋希望教会华文堂",
        "亚罗士打北方之星教会",
        "生命樹平安堂/ 中華平安宣教協會",
        "FGA Holy Word",
        "Betong Blessed Church",
        "安樂聖教會",
        "林大中牧师",
        "赞美教会中文部",
        "怡保房角石教会 Cornerstone Church Ipoh",
        "凯旋教会 Victory Christian Centre",
        "文良港社区教会 Setapak Community Church",
        "大坪林靈糧福音中心",
        "城市星光教会",
        "Every Nation Church Malaysia",
        "Praise City Church, Kuala Lumpur",
        "Ecclesia Theological Seminary",
        "Full Gospel Assembly ",
        "FGAKL 吉隆坡全备福音堂",
        "恩典青年中心 Charis Youth Centre",
        "卓越欣荣家庭教会 Joyous Glorious Community Church",
        "First Assembly of God Church, Kuala Lumpur",
        "马来西亚使徒性网络中心 Malaysia Apostolic Network",
        "KKBOL 亚庇灵粮堂使徒性中心",
        "FGA CYC",
        "末世先鋒 - Kingdom for Jesus",
        "OGBC - 高雄總會",
        "丰收教會",
        "牧林教會",
        "葛兆昕",
        "台灣亞洲基督教會cwca",
        "基督教會磐石之家",
        "拿督公",
        "金宝恩典社区中心",
        "2019超自然大能與使徒性教會 特會",
        "南崁基督之家",
        "看見神同在基督教會",
        "圣灵医治释放事工",
        "末世先锋",
        "加略山门徒教会",
        "翁乔治牧师",
        "台北希望教會 HOPE Church Taipei",
        "GTPJ 喜信堂",
        "黃雅各牧師(Rev. James Wong),",
        "Hope International Ministries",
        "基督教金寶希望教會",
        "Hope Church Kampar 金宝希望教会",
        "新山全备",
        "梅坚成",
        "建道基督教会 WORD Community Church",
        "Bible College of Malaysia",
        "马来西亚圣经学院",
        "马来西亚爱修国际领袖学院",
        "JCC - 日本華人基督徒中心/日本華人クリスチャンセンター",
        " Seventh Day Adventist Church PJ-Chinese",
        "Klang Adventist Ping An Church ",
        "J世代青少年特会 J-Generation Youth Conference",
        "新马教会更新团契",
        "马来西亚全备福音训练中心",
        "翁乔治牧师",
        "麻坡丰收教会",
        "士古来丰收教会",
        "巴淡丰收教会",
        "聽障福音事工",
        "六寶教會",
        "財團法人基督教浸信會六寶禮拜堂",
        "先知預言祭壇",
        "信心帳幕教會主任牧師歐大衛",
        "歐大衛",
        "全球活信教會",
        "奈國信心帳幕教會（Faith Tabernacle）",
        "汐止愛加倍教會",
        "多納古納達望紅色教堂",
        "台中伊甸園教會",
        "旺角潮語浸信會",
        "恩典聖經學院",
        "迦南家 KKC Canaan Family",
        "靈盈學院 MISF Makarios Institute for Spiritual Formation",
        "內湖城市之光教會",
        "Recreation Ministry Union (H.K.)",
        "上帝的教會世界福音宣教協會",
        "應許之地國際事工",
        "徐秀慧",
        "張苓苓",
        "ALEMC 豐盛生命福音宣教會",
        "葉國才",
        "GFMM,復興宣教事工 Global Fire Missions Ministries",
        "吧生恩典堂青少年牧区 SMART YOUTH-Youth Ministry",
        "約書亞三重信心教會",
        "花蓮蒙福純福音教會",
        "桃園佳音教會",
        "專注榮耀國度敬拜中心",
        "T4t台灣使徒中心",
        "以利亞先知學校-台灣校區Mount Zion Christian Church",
        "陳騏 使徒事工 ChenChi APM",
        "高雄市信心福音教會",
        "天糧教會",
        "天粮教会",
        "梧棲敬拜中心",
        "基督大使教會迦南堂",
        "台宣得勝教會。",
        "台灣原住民靈恩基督教與宣教拓展--蒙恩宣教中心",
        "蒙恩宣教中心",
        "新莊更新教會",
        "鳳山浸信會",
        "高雄市真愛教會",
        "高雄天泉教會",
        "高雄武昌教會",
        "竹東懷恩教會",
        "豐盛教會",
        "愛復活-2014復活節聯合慶典",
        "北埔教會",
        "豐原伯特利教會",
        "基隆馨香教會",
        "高雄基督之家",
        "大安福音中心",
        "大使命教會新堂",
        "得勝教會",
        "燈塔基督教教會",
        "夏凱納敬拜中心",
        "台北靈糧神學院",
        "吳炳偉  （牧師）",
        "City Celebration Centre 城市欢庆教会",
        "加略山城市教会",
        "全福之夜",
        "中壢禮拜堂",
        "松山禮拜堂",
        "磐石基督教会",
        "新向教會",
        "大都會國際兒童事工",
        "FY 生命小组",
        "F.G.A Batu Pahat 全备福音堂",
        "竹南純福音教會",
        "Holy Seal 611 BOL",
        "約書亞樂團",
        "吉隆坡荣耀堂",
        "四方福音会",
        "在基督里神的教会",
        "台湾福音宣教会",
        "爱天宣教会",
        "耶稣晨星教会",
        "鄭明析牧師",
        "攝理教我向前走",
        "CMC • 天命教会",
        "基督教福音宣教會",
        "明析成功語錄",
        "卫理凯胜之家 生命塑造中心 Methodist Victory Home",
        "怡保仁爱社区教会 IACC",
        "國度使徒中心網絡",
        "Corner Stone Media",
        "Salvation Army ",
        "Grace AOG Church Penang",
        "台中興起教會",
        "西布倫使徒中心",
        "城门基督教会",
        "黄约翰",
        "国度生命城市教会",
        "台中平安堂",
        "Hakka Methodist Church 卫理公会客音天恩",
        "FGA",
        "FaithLine International Ministries",
        "Full Gospel Church",
        "Harvest Church",
        "TCOC 台灣國際基督教會",
        "金陵协和神学院",
        "Calvary Community Church Tmn Universiti & Kangkar Pulai",
        "Alliance of Pentecostal & Charismatic Churches of Singapore",
        "Bethel Bible College of the Assemblies of God ",
        "新加坡教会丰收 Church of Singapore Harvest",
        "The Pentecostal Church Of God Hong Kong Antioch Church ",
        "神召會元朗福音中心（元福）",
        "內壢榮美崇真堂",
        "靈糧神學院",
        "River of Life Christian Church",
        "台南基督恩典教會",
        "武昌生命泉教会",
        "耶穌基督的門徒",
    ];

    public function load(App $app): void
    {
        $this->app = $app;
    }

    // 挑选正面派别
    public function selectFriendFaith($path)
    {

        $lines = getLine($path);

        $good_result_ids = [];
        foreach ($lines as $line) {

            foreach ($this->good_keywords as $keyword) {
                if (str_contains($line, $keyword)) {
                    $good_results[] = $line;

                    // 收集正面ID
                    $good_result_ids[] = explode("\t", $line)[0];
                    continue 2;
                }
            }

            foreach ($this->eval_keywords as $keyword) {
                if (str_contains($line, $keyword)) {
                    $bad_results[] = $line;
                    continue 2;
                }
            }

            $notsure_results[] = $line;
        }

        $path = FAITH_OUTPUT_PAHT . CURRENT_DATE . CURRENT_TIME . " good_result.tsv";
        file_put_contents($path, implode(PHP_EOL, $good_results));
        $this->app->info(sprintf("正面派别 %d 个", count($good_results)));

        $path = FAITH_OUTPUT_PAHT . CURRENT_DATE . CURRENT_TIME . " good_result_id.tsv";
        file_put_contents($path, implode(PHP_EOL, $good_result_ids));

        $path = FAITH_OUTPUT_PAHT . CURRENT_DATE . CURRENT_TIME . " bad_result.tsv";
        file_put_contents($path, implode(PHP_EOL, $bad_results));
        $this->app->info(sprintf("反面派别 %d 个", count($bad_results)));

        $path = FAITH_OUTPUT_PAHT . CURRENT_DATE . CURRENT_TIME . " notsure_result.tsv";
        file_put_contents($path, implode(PHP_EOL, $notsure_results));
        $this->app->info(sprintf("无派别 %d 个", count($notsure_results)));
    }

    // 挑选天主教、基督教
    public function selectType($filePath, $type)
    {
        $lines = getLine($filePath);

        $badTypes       = [];
        $asideTypes     = [];
        $christianTypes = [];

        $myPreg = "/" . implode("|", $this->myCitys) . "/";
        $twPreg = "/" . implode("|", $this->twCitys) . "/";

        foreach ($lines as $line) {
            $lineArr = explode("\t", $line);

            $id     = $lineArr[0] ?? "";
            $faith  = $lineArr[1] ?? "";

            if (empty($id)) {
                continue;
            }

            if (
                empty($faith) ||
                $faith === '❌' ||
                str_contains($faith, "佛") ||
                str_contains($faith, "印度") ||
                str_contains($faith, "伊斯兰")
            ) {
                $badTypes[] = $line;
                continue;
            }

            // 当挑选 马来西亚 或 非马来西亚 的账号时，匹配地区
            if ($type === "my" && !preg_match($myPreg, $line)) {
                $badTypes[] = $line;
                continue;
            } else if ($type === "tw" && !preg_match($twPreg, $line)) {
                $badTypes[] = $line;
                continue;
            }

            if (str_contains($faith, "❌")) {
                $asideTypes[] = $line;
                continue;
            }

            if (str_contains($faith, "基督") || str_contains($faith, "天主")) {
                $christianTypes[] = $line;
                continue;
            }

            // 收集特殊情况
            $badTypes[] = $line;
        }

        $badPath  = FAITH_OUTPUT_PAHT . CURRENT_DATE . CURRENT_TIME . " bad.tsv";
        file_put_contents($badPath, implode(PHP_EOL, $badTypes));

        $asidePath    = FAITH_OUTPUT_PAHT . CURRENT_DATE . CURRENT_TIME . " aside.tsv";
        file_put_contents($asidePath, implode(PHP_EOL, $asideTypes));

        $christianPath = FAITH_OUTPUT_PAHT . CURRENT_DATE . CURRENT_TIME . " christian.tsv";
        file_put_contents($christianPath, implode(PHP_EOL, $christianTypes));
    }
}
