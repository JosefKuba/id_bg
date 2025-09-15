<?php

date_default_timezone_set('Europe/Amsterdam');

define("CURRENT_TIME",  date("Y-m-d H-i-s"));
define("CURRENT_DATE",  date("Y-m-d"));

const ROOT_PATH = __DIR__ . "/../";

const DATA_PATH = ROOT_PATH . "data/";

const ID_INPUT_PATH = ROOT_PATH . "data/id/input/";

const ID_OUTPUT_PATH = ROOT_PATH . "data/id/output/";

const ID_BACKUP_PATH = ROOT_PATH . "data/id/backup/";

const ID_OUTPUT_COLLECT_PATH = ID_OUTPUT_PATH . "collect/";

const ID_OUTPUT_ASIDE_PATH = ID_OUTPUT_PATH . "aside/";

const ID_OUTPUT_EXCLUDE_PATH = ID_OUTPUT_PATH . "exclude/";

// page const
const PAGE_INPUT_PATH = ROOT_PATH . "data/page/input/";

const PAGE_OUTPUT_PATH = ROOT_PATH . "data/page/output/";

const PAGE_BACKUP_PATH = ROOT_PATH . "data/page/backup/";

const PAGE_OUTPUT_EXCLUDE_PATH = ROOT_PATH . "data/page/output/exclude/";

const PAGE_OUTPUT_LOCATION_PATH = ROOT_PATH . "data/page/output/location/";

const PAGE_OUTPUT_FUNSLOWER_PATH = ROOT_PATH . "data/page/output/funslower/";

const PAGE_OUTPUT_A_CLASS_PATH = ROOT_PATH . "data/page/output/A/";

const PAGE_OUTPUT_B_CLASS_PATH = ROOT_PATH . "data/page/output/B/";

const PAGE_OUTPUT_C_CLASS_PATH = ROOT_PATH . "data/page/output/C/";

// group const
const GROUP_INPUT_PATH = ROOT_PATH . "data/group/input/";

const GROUP_OUTPUT_PATH = ROOT_PATH . "data/group/output/";

const GROUP_BACKUP_PATH = ROOT_PATH . "data/group/backup/";

const GROUP_OUTPUT_PUBLIC_PATH = ROOT_PATH . "data/group/output/public/";

const GROUP_OUTPUT_PRIVATE_PATH = ROOT_PATH . "data/group/output/private/";

const GROUP_OUTPUT_FUNSLOWER_PATH = ROOT_PATH . "data/group/output/funslower/";

const GROUP_OUTPUT_EXCLUDE_PATH = ROOT_PATH . "data/group/output/exclude/";


// post const
const POST_INPUT_PATH = ROOT_PATH . "data/post/input/";

const POST_OUTPUT_PATH = ROOT_PATH . "data/post/output/";

const POST_BACKUP_PATH = ROOT_PATH . "data/post/backup/";


// 好友的好友
const FRIEND_INPUT_PATH = ROOT_PATH . "data/friend/input/";

const FRIEND_OUTPUT_PATH = ROOT_PATH . "data/friend/output/";

const FRIEND_BACKUP_PATH = ROOT_PATH . "data/friend/backup/";

// 头像
const AVATER_INPUT_PATH = ROOT_PATH . "data/avater/input/";

const AVATER_OUTPUT_PATH = ROOT_PATH . "data/avater/output/";

const AVATER_BACKUP_PATH = ROOT_PATH . "data/avater/backup/";

// 检测信仰
const FAITH_INPUT_PAHT = ROOT_PATH . "data/faith/input/";

const FAITH_OUTPUT_PAHT = ROOT_PATH . "data/faith/output/";

const FAITH_BACKUP_PAHT = ROOT_PATH . "data/faith/backup/";

// 挑选地区
const AREA_INPUT_PATH = ROOT_PATH . "data/area/input/";

const AREA_OUTPUT_PATH = ROOT_PATH . "data/area/output/";

const AREA_BACKUP_PATH = ROOT_PATH . "data/area/backup/";

// 处理链接
const LINK_INPUT_PATH = ROOT_PATH . "data/link/input/";

const LINK_OUTPUT_PATH = ROOT_PATH . "data/link/output/";

const LINK_BACKUP_PATH = ROOT_PATH . "data/link/backup/";

// 处理关键词
const KEYWORD_INPUT_PATH = ROOT_PATH . "data/keyword/input/";

const KEYWORD_OUTPUT_PATH = ROOT_PATH . "data/keyword/output/";

const KEYWORD_BACKUP_PATH = ROOT_PATH . "data/keyword/backup/";

// RC库
const RC_INPUT_PATH = ROOT_PATH . "data/rc/input/";

const RC_OUTPUT_PATH = ROOT_PATH . "data/rc/output/";

const RC_BACKUP_PATH = ROOT_PATH . "data/rc/backup/";

// 名字
const NAME_INPUT_PATH = ROOT_PATH . "data/name/input/";

const NAME_OUTPUT_PATH = ROOT_PATH . "data/name/output/";

const NAME_BACKUP_PATH = ROOT_PATH . "data/name/backup/";

// IG
const IG_INPUT_PATH = ROOT_PATH . "data/instagram/input/";

const IG_OUTPUT_PATH = ROOT_PATH . "data/instagram/output/";

const IG_BACKUP_PATH = ROOT_PATH . "data/instagram/backup/";

// WhatsApp
const WAP_INPUT_PATH = ROOT_PATH . "data/whatsapp/input/";

const WAP_OUTPUT_PATH = ROOT_PATH . "data/whatsapp/output/";

const WAP_BACKUP_PATH = ROOT_PATH . "data/whatsapp/backup/";

// google 表格
const SHEET_INPUT_PATH = ROOT_PATH . "data/sheet/input/";

const SHEET_OUTPUT_PATH = ROOT_PATH . "data/sheet/output/";

const SHEET_BACKUP_PATH = ROOT_PATH . "data/sheet/backup/";

// YTB
const YTB_INPUT_PATH = ROOT_PATH . "data/youtube/input/";

const YTB_OUTPUT_PATH = ROOT_PATH . "data/youtube/output/";

const YTB_BACKUP_PATH = ROOT_PATH . "data/youtube/backup/";

// FB
const FB_INPUT_PATH = ROOT_PATH . "data/facebook/input/";

const FB_OUTPUT_PATH = ROOT_PATH . "data/facebook/output/";

const FB_BACKUP_PATH = ROOT_PATH . "data/facebook/backup/";

// Chatbot
const CHATBOT_INPUT_PATH = ROOT_PATH . "data/chatbot/input/";

const CHATBOT_OUTPUT_PATH = ROOT_PATH . "data/chatbot/output/";

const CHATBOT_BACKUP_PATH = ROOT_PATH . "data/chatbot/backup/";


// 已刷脸的ID
const ID_DB_FILE = ROOT_PATH . "data/database/ids";

// 马来西亚ID
const MY_ID_DB_FILE = ROOT_PATH . "data/database/ids_my";

// 深宗好友id 的文本备份文件
const FRIENDS_DB_FILE = ROOT_PATH . "data/database/ids_friends";

// 专页的文本备份文件
const PAGE_DB_FILE = ROOT_PATH . "data/database/pages";

// 用户小组的文本备份文件
const USER_GROUPS_DB_FILE = ROOT_PATH . "data/database/groups_user";

// 查考小组的备份文件
const SEARCH_GROUPS_DB_FILE = ROOT_PATH . "data/database/groups_search";

// 可推广小组备份文件
const PROMOTE_GROUPS_DB_FILE = ROOT_PATH . "data/database/groups_promote";


// 导用户的好友，用户ID备份文件
const GROUPS_USER_ID_DB_FILE = ROOT_PATH . "data/database/ids_groups";

// 好友的好友ID目录
const FRIEND_DB_FOLDER = ROOT_PATH . "data/database/friends/";

// 好友的好友ID临时目录
const FRIEND_DB_FOLDER_TMP = ROOT_PATH . "data/database/friends_tmp/";

// 存放所有好友ID的目录
const FRIEND_FILES_FOLDER = ROOT_PATH . "_dev/id_toolbox/friends/";

// 存放过滤过线索名字的所有好友ID的目录
const FRIEND_FILES_PURE_FOLDER = ROOT_PATH . "data/database/friends_files_pure/";
