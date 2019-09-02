<?php
namespace libraries\constant;

/**
 * Class TFConst
 *
 * 타이드스퀘어 VS 트레블포레스트 상수
 *
 * @package libraries\constant
 */
class TFConst
{
    const TS_ACCESS_TOKEN       = '7C22CD25B2E92E0CF79DBDBD31FFDD161BB7486F34E582AD9C1E6487F5C94851CA3FD9B449DC06E8FFC843AB86ABB3EEF54708AC74CA6DEDEB71EAE00015CC3D';
    const TS_DEV_ACCESS_TOKEN   = 'FA12624437284939AAFEEE43625BFB55AD1E7476A9D01647DEA64DF7B3E5B1AB7DC16C55113251D7E9CCFA42FF15FB4F2E0A7781E2858BB6301D81C930CA3D48';
    const TS_SUPPLIER_CODE      = 'TRAVELFRST';

    const TS_WEBHOOK_URL     = 'https://tnaapi.tourvis.com';
    const TS_WEBHOOK_DEV_URL = 'https://devtnaadmin.tourvis.com';

    const TS_RESERVE_WEBHOOK_URI = '/apiary/common/webhook/v1/booking';
    const TS_PRODUCT_WEBHOOK_URI = '/apiary/common/webhook/v1/product';

    const TF_URL = 'https://www.travelforest.co.kr/api/tidesquare';
    const TF_GET_AREA_URI = '/areas';
    const TF_GET_PRODUCTS_URI = '/products';
    const TF_GET_PRODUCT_URI = '/product';
    const TF_BOOK_ADD_URL = 'https://www.travelforest.co.kr/api/tidesquare/book';
    const TF_BOOK_PROC_URL = 'https://www.travelforest.co.kr/api/tidesquare/books';

    const REDIS_TF_SESSION = 15;
    const REDIS_TF_AREA_REDIS_KEY = 'tf_area';
    const REDIS_TF_PRODUCTS_REDIS_KEY = 'tf_product_list';
    const REDIS_TF_PRODUCT_REDIS_KEY = 'tf_product';
    const REDIS_TF_SUB_PRODUCT_REDIS_KEY = 'tf_sub_product';
    const REDIS_TF_BOOKING_REDIS_KEY = 'tf_booking';

    const RESERVE_STATUS_RESV = 'RESERVED'; // 예약접수, 고객이 상품에 대한 주문을 한 상태입니다.
    const RESERVE_STATUS_CFRM = 'CONFIRM';  // 예약확정, 주문 상품의 예약이 확정된 상태입니다.
    const RESERVE_STATUS_WAIT = 'WAITING';  // 확정대기
    const RESERVE_STATUS_CNCL = 'CANCEL';   // 예약취소

    const CANCEL_STATUS_CFRM = self::RESERVE_STATUS_CNCL;
    const CANCEL_STATUS_WAIT =  'CANCEL_WAIT';
    const CANCEL_STATUS_DENY = 'CANCEL_DECLINED';
    const STATUS_ERROR = 'ERROR';

    const TF_BOOK_CD_ORD = 'order';   // 예약접수, 고객이 상품에 대한 주문을 한 상태입니다.
    const TF_BOOK_CD_REQ = 'request'; // 예약가능, 주문 상품이 예약 가능한 상태입니다.
    const TF_BOOK_CD_INS = 'instant'; // 확정가능, 주문 상품이 즉시 확정 가능한 상태입니다. 즉시 확정가능한 주문이더라도 확정까지는 최소 10초이상의 시간이 소요됩니다.
    const TF_BOOK_CD_BOK = 'book';    // 예약진행, 주문 상품이 예약 진행 중입니다. 'request'였던 경우 해당 담당자가 예약을 진행중이며, 'instant'였던 경우 시스템에서 예약을 진행중인 상태입니다.
    const TF_BOOK_CD_CFM = 'confirm'; // 예약확정, 주문 상품의 예약이 확정된 상태입니다.
    const TF_BOOK_CD_CLS = 'close';   // 예약불가, 주문 상품의 예약이 현지 사정등으로 인해 예약이 불가능한 상태입니다.
    const TF_BOOK_CD_CCL = 'cancel';  // 예약취소, 고객의 요청으로 인해 주문 상품의 예약이 취소된 상태입니다.

    const TS_OPTION_TARGET_PER_BOOK = 1;
    const TS_OPTION_TARGET_PER_PAX  = 2;

    const TS_OPTION_TYPE_LIST       = 1;
    const TS_OPTION_TYPE_NUM        = 3;
    const TS_OPTION_TYPE_TEXT       = 4;
    const TS_OPTION_TYPE_BOOL       = 5;
    const TS_OPTION_TYPE_DATE       = 6;
    const TS_OPTION_TYPE_TIME       = 10;
    const TS_OPTION_TYPE_TIMESTAMP  = 11;

    const TF_ADULT_LABEL     = '성인';
    const TF_TRANSFER_LABEL  = '차량';
    const TF_TEENAGE_LABEL   = '청소년';
    const TF_CHILD_LABEL     = '아동';
    const TF_YOUNGSTER_LABEL = '어린이';
    const TF_INFANT_LABEL    = '유아';
    const TS_INFANT_LABEL    = '영유아';

    const TF_TOKYO_CODE             = 1;
    const TF_OSAKA_CODE             = 2;
    const TF_FUKUOKA_CODE           = 3;
    const TF_BANGKOK_CODE           = 4;
    const TF_PATTAYA_CODE           = 5;
    const TF_PHUKET_CODE            = 6;
    const TF_CHIANGMAI_CODE         = 7;
    const TF_KOSAMUI_CODE           = 8;
    const TF_KRABI_CODE             = 9;
    const TF_KANCHANABURI_CODE      = 11;
    const TF_AYUTTHAYA_CODE         = 12;
    const TF_BORACAY_CODE           = 13;
    const TF_CEBU_CODE              = 14;
    const TF_BOHOL_CODE             = 15;
    const TF_MANILA_CODE            = 16;
    const TF_PALAWAN_CODE           = 17;
    const TF_HONGKONG_CODE          = 18;
    const TF_SINGAPORE_CODE         = 20;
    const TF_MACAU_CODE             = 19;
    const TF_VIENTIANE_CODE         = 21;
    const TF_VANGVIENG_CODE         = 22;
    const TF_LUANGPRABANG_CODE      = 23;
    const TF_PAKSE_CODE             = 24;
    const TF_HANOI_HALONGBAY_CODE   = 25;
    const TF_DANANG_CODE            = 26;
    const TF_HOCHIMHIN_CODE         = 27;
    const TF_SIEMREAP_CODE          = 28;
    const TF_KUALALUMPUR_CODE       = 29;
    const TF_LANGKAWI_CODE          = 30;
    const TF_KOTAKINABALU_CODE      = 31;
    const TF_TAIPEI_CODE            = 33;
    const TF_BALI_CODE              = 34;
    const TF_LOMBOK_CODE            = 35;
    const TF_DUBAI_CODE             = 38;
    const TF_ABUDHABI_CODE          = 39;
    const TF_DELHI_CODE             = 40;
    const TF_AGRA_CODE              = 41;
    const TF_JAIPUR_CODE            = 42;
    const TF_VARANASI_CODE          = 43;
    const TF_KHAJURAHO_CODE         = 44;
    const TF_JAISAMLMER_CODE        = 45;
    const TF_SRILANKA_CODE          = 55;
    const TF_POKARA_CODE            = 56;
    const TF_KATUMANDU_CODE         = 57;
    const TF_GUAM_CODE              = 58;
    const TF_SAIPAN_CODE            = 59;
    const TF_SYDNEY_CODE            = 60;
    const TF_CAIRNS_CODE            = 61;
    const TF_MELBOURNE_CODE         = 62;
    const TF_BRISBANE_CODE          = 63;
    const TF_GOLDCOAST_CODE         = 64;
    const TF_ULURU_CODE             = 65;
    const TF_LONDON_CODE            = 71;
    const TF_PARIS_CODE             = 72;
    const TF_ROME_CODE              = 73;
    const TF_FIRENZE_CODE           = 74;
    const TF_VENICE_CODE            = 75;
    const TF_INTERLAKEN_CODE        = 76;
    const TF_MADRID_CODE            = 77;
    const TF_BARCELONA_CODE         = 78;
    const TF_VIENNA_CODE            = 79;
    const TF_FRANKFURT_CODE         = 80;
    const TF_PRAGUE_CODE            = 82;
    const TF_ISTANBUL_CODE          = 84;
    const TF_NEWYORK_CODE           = 85;
    const TF_HAWAIIHONOLULU_CODE    = 86;
    const TF_LASVEGAS_CODE          = 87;
    const TF_LOSANGELES_CODE        = 88;
    const TF_SANFRANCISCO_CODE      = 89;
    const TF_NHATRANG_CODE          = 90;
    const TF_PALAU_CODE             = 91;
    const TF_HOIAN_CODE             = 92;
    const TF_HUAHIN_CODE            = 93;
    const TF_MOSCOW_CODE            = 94;
    const TF_VLADIVOSTOK_CODE       = 95;
    const TF_MUINE_CODE             = 96;
    const TF_SEVILLE_CODE           = 97;
    const TF_GRANADA_CODE           = 98;
    const TF_MALAGA_CODE            = 99;
    const TF_PHUQUOC_CODE           = 100;
    const TF_DRESDEN_CODE           = 102;
    const TF_HALLSTATT_CODE         = 103;
    const TF_CESKYKRUMLOV_CODE      = 104;
    const TF_ZAGREB_CODE            = 105;
    const TF_DUBROVNIK_CODE         = 106;
    const TF_PLITVICE_CODE          = 107;
    const TF_LJUBLJANA_CODE         = 109;
    const TF_ZURICH_CODE            = 110;
    const TF_LISBON_CODE            = 111;
    const TF_BUDAPEST_CODE          = 112;
    const TF_SPLIT_CODE             = 114;
    const TF_BLED_CODE              = 116;
    const TF_CAPPADOCIA_CODE        = 117;
    const TF_AMALFI_CODE            = 118;

    const TS_LOC_ESA = 'SA'; // 동남아
    const TS_LOC_CHA = 'CN'; // 중국
    const TS_LOC_JAP = 'JP'; // 일본
    const TS_LOC_EUR = 'EU'; // 유럽
    const TS_LOC_AME = 'AM'; // 미주
    const TS_LOC_SPO = 'NP'; // 남태평양

    const TS_AREA_LOC_TXT = 'location';
    const TS_AREA_CITY_TXT = 'city';

    const TS_AREA_CODE = [
        self::TF_TOKYO_CODE => [
            self::TS_AREA_LOC_TXT => self::TS_LOC_JAP,
            self::TS_AREA_CITY_TXT => 'TYO',
        ],
        self::TF_OSAKA_CODE => [
            self::TS_AREA_LOC_TXT => self::TS_LOC_JAP,
            self::TS_AREA_CITY_TXT => 'OSA',
        ],
        self::TF_FUKUOKA_CODE => [
            self::TS_AREA_LOC_TXT => self::TS_LOC_JAP,
            self::TS_AREA_CITY_TXT => 'FUK',
        ],
        self::TF_BANGKOK_CODE => [
            self::TS_AREA_LOC_TXT => self::TS_LOC_ESA,
            self::TS_AREA_CITY_TXT => 'BKK',
        ],
        self::TF_PATTAYA_CODE => [
            self::TS_AREA_LOC_TXT => self::TS_LOC_ESA,
            self::TS_AREA_CITY_TXT => 'PYX',
        ],
        self::TF_PHUKET_CODE => [
            self::TS_AREA_LOC_TXT => self::TS_LOC_ESA,
            self::TS_AREA_CITY_TXT => 'HKT',
        ],
        self::TF_CHIANGMAI_CODE => [
            self::TS_AREA_LOC_TXT => self::TS_LOC_ESA,
            self::TS_AREA_CITY_TXT => 'CNX',
        ],
        self::TF_KOSAMUI_CODE => [
            self::TS_AREA_LOC_TXT => self::TS_LOC_ESA,
            self::TS_AREA_CITY_TXT => 'USM',
        ],
        self::TF_KRABI_CODE => [
            self::TS_AREA_LOC_TXT => self::TS_LOC_ESA,
            self::TS_AREA_CITY_TXT => 'KBV',
        ],
        self::TF_KANCHANABURI_CODE => [
            self::TS_AREA_LOC_TXT => self::TS_LOC_ESA,
            self::TS_AREA_CITY_TXT => 'KNC',
        ],
        self::TF_AYUTTHAYA_CODE => [
            self::TS_AREA_LOC_TXT => self::TS_LOC_ESA,
            //self::TS_AREA_CITY_TXT => 'AUB',
            self::TS_AREA_CITY_TXT => 'BKK',
        ],
        self::TF_BORACAY_CODE => [
            self::TS_AREA_LOC_TXT => self::TS_LOC_ESA,
            self::TS_AREA_CITY_TXT => 'KLO',
        ],
        self::TF_CEBU_CODE => [
            self::TS_AREA_LOC_TXT => self::TS_LOC_ESA,
            self::TS_AREA_CITY_TXT => 'CEB',
        ],
        self::TF_BOHOL_CODE => [
            self::TS_AREA_LOC_TXT => self::TS_LOC_ESA,
            self::TS_AREA_CITY_TXT => 'BB6',
        ],
        self::TF_MANILA_CODE => [
            self::TS_AREA_LOC_TXT => self::TS_LOC_ESA,
            self::TS_AREA_CITY_TXT => 'MNL',
        ],
        self::TF_PALAWAN_CODE => [
            self::TS_AREA_LOC_TXT => self::TS_LOC_ESA,
            self::TS_AREA_CITY_TXT => 'PLN',
        ],
        self::TF_HONGKONG_CODE => [
            self::TS_AREA_LOC_TXT => self::TS_LOC_ESA,
            self::TS_AREA_CITY_TXT => 'HKG',
        ],
        self::TF_MACAU_CODE => [
            self::TS_AREA_LOC_TXT => self::TS_LOC_ESA,
            self::TS_AREA_CITY_TXT => 'MFM',
        ],
        self::TF_SINGAPORE_CODE => [
            self::TS_AREA_LOC_TXT => self::TS_LOC_ESA,
            self::TS_AREA_CITY_TXT => 'SIN',
        ],
        self::TF_VIENTIANE_CODE => [
            self::TS_AREA_LOC_TXT => self::TS_LOC_ESA,
            self::TS_AREA_CITY_TXT => 'VTE',
        ],
        self::TF_VANGVIENG_CODE => [
            self::TS_AREA_LOC_TXT => self::TS_LOC_ESA,
            self::TS_AREA_CITY_TXT => 'VAV',
        ],
        self::TF_LUANGPRABANG_CODE => [
            self::TS_AREA_LOC_TXT => self::TS_LOC_ESA,
            self::TS_AREA_CITY_TXT => 'LPQ',
        ],
        self::TF_PAKSE_CODE => [
            self::TS_AREA_LOC_TXT => self::TS_LOC_ESA,
            self::TS_AREA_CITY_TXT => 'PKZ',
        ],
        self::TF_HANOI_HALONGBAY_CODE => [
            self::TS_AREA_LOC_TXT => self::TS_LOC_ESA,
            self::TS_AREA_CITY_TXT => 'HAN',
        ],
        self::TF_DANANG_CODE => [
            self::TS_AREA_LOC_TXT => self::TS_LOC_ESA,
            self::TS_AREA_CITY_TXT => 'DAD',
        ],
        self::TF_HOCHIMHIN_CODE => [
            self::TS_AREA_LOC_TXT => self::TS_LOC_ESA,
            self::TS_AREA_CITY_TXT => 'SGN',
        ],
        self::TF_SIEMREAP_CODE => [
            self::TS_AREA_LOC_TXT => self::TS_LOC_ESA,
            self::TS_AREA_CITY_TXT => 'REP',
        ],
        self::TF_KUALALUMPUR_CODE => [
            self::TS_AREA_LOC_TXT => self::TS_LOC_ESA,
            self::TS_AREA_CITY_TXT => 'KUL',
        ],
        self::TF_LANGKAWI_CODE => [
            self::TS_AREA_LOC_TXT => self::TS_LOC_ESA,
            self::TS_AREA_CITY_TXT => 'LGK',
        ],
        self::TF_KOTAKINABALU_CODE => [
            self::TS_AREA_LOC_TXT => self::TS_LOC_ESA,
            self::TS_AREA_CITY_TXT => 'BKI',
        ],
        self::TF_TAIPEI_CODE => [
            self::TS_AREA_LOC_TXT => self::TS_LOC_ESA,
            self::TS_AREA_CITY_TXT => 'TPE',
        ],
        self::TF_BALI_CODE => [
            self::TS_AREA_LOC_TXT => self::TS_LOC_ESA,
            self::TS_AREA_CITY_TXT => 'DPS',
        ],
        self::TF_LOMBOK_CODE => [
            self::TS_AREA_LOC_TXT => self::TS_LOC_ESA,
            self::TS_AREA_CITY_TXT => 'AMI',
        ],
        self::TF_DUBAI_CODE => [
            self::TS_AREA_LOC_TXT => self::TS_LOC_ESA,
            self::TS_AREA_CITY_TXT => 'DXB',
        ],
        self::TF_ABUDHABI_CODE => [
            self::TS_AREA_LOC_TXT => self::TS_LOC_ESA,
            self::TS_AREA_CITY_TXT => 'AUH',
        ],
        self::TF_DELHI_CODE => [
            self::TS_AREA_LOC_TXT => self::TS_LOC_ESA,
            self::TS_AREA_CITY_TXT => 'DEL',
        ],
        self::TF_AGRA_CODE => [
            self::TS_AREA_LOC_TXT => self::TS_LOC_ESA,
            self::TS_AREA_CITY_TXT => 'AGR',
        ],
        self::TF_JAIPUR_CODE => [
            self::TS_AREA_LOC_TXT => self::TS_LOC_ESA,
            self::TS_AREA_CITY_TXT => 'JAI',
        ],
        self::TF_VARANASI_CODE => [
            self::TS_AREA_LOC_TXT => self::TS_LOC_ESA,
            self::TS_AREA_CITY_TXT => 'VNS',
        ],
        self::TF_KHAJURAHO_CODE => [
            self::TS_AREA_LOC_TXT => self::TS_LOC_ESA,
            self::TS_AREA_CITY_TXT => 'HJR',
        ],
        self::TF_JAISAMLMER_CODE => [
            self::TS_AREA_LOC_TXT => self::TS_LOC_ESA,
            self::TS_AREA_CITY_TXT => 'JSA',
        ],
        self::TF_SRILANKA_CODE => [
            self::TS_AREA_LOC_TXT => self::TS_LOC_ESA,
            self::TS_AREA_CITY_TXT => 'CMB',
        ],
        self::TF_POKARA_CODE => [
            self::TS_AREA_LOC_TXT => self::TS_LOC_ESA,
            self::TS_AREA_CITY_TXT => 'PKR',
        ],
        self::TF_KATUMANDU_CODE => [
            self::TS_AREA_LOC_TXT => self::TS_LOC_ESA,
            self::TS_AREA_CITY_TXT => 'KTM',
        ],
        self::TF_GUAM_CODE => [
            self::TS_AREA_LOC_TXT => self::TS_LOC_SPO,
            self::TS_AREA_CITY_TXT => 'GUM',
        ],
        self::TF_SAIPAN_CODE => [
            self::TS_AREA_LOC_TXT => self::TS_LOC_SPO,
            self::TS_AREA_CITY_TXT => 'SPN',
        ],
        self::TF_SYDNEY_CODE => [
            self::TS_AREA_LOC_TXT => self::TS_LOC_SPO,
            self::TS_AREA_CITY_TXT => 'SYD',
        ],
        self::TF_CAIRNS_CODE => [
            self::TS_AREA_LOC_TXT => self::TS_LOC_SPO,
            self::TS_AREA_CITY_TXT => 'CNS',
        ],
        self::TF_MELBOURNE_CODE => [
            self::TS_AREA_LOC_TXT => self::TS_LOC_SPO,
            self::TS_AREA_CITY_TXT => 'MEL',
        ],
        self::TF_BRISBANE_CODE => [
            self::TS_AREA_LOC_TXT => self::TS_LOC_SPO,
            self::TS_AREA_CITY_TXT => 'BNE',
        ],
        self::TF_GOLDCOAST_CODE => [
            self::TS_AREA_LOC_TXT => self::TS_LOC_SPO,
            self::TS_AREA_CITY_TXT => 'OOL',
        ],
        self::TF_ULURU_CODE => [
            self::TS_AREA_LOC_TXT => self::TS_LOC_SPO,
            self::TS_AREA_CITY_TXT => 'ULR',
        ],
        self::TF_LONDON_CODE => [
            self::TS_AREA_LOC_TXT => self::TS_LOC_EUR,
            self::TS_AREA_CITY_TXT => 'LON',
        ],
        self::TF_PARIS_CODE => [
            self::TS_AREA_LOC_TXT => self::TS_LOC_EUR,
            self::TS_AREA_CITY_TXT => 'PAR',
        ],
        self::TF_ROME_CODE => [
            self::TS_AREA_LOC_TXT => self::TS_LOC_EUR,
            self::TS_AREA_CITY_TXT => 'ROM',
        ],
        self::TF_FIRENZE_CODE => [
            self::TS_AREA_LOC_TXT => self::TS_LOC_EUR,
            self::TS_AREA_CITY_TXT => 'FLR',
        ],
        self::TF_VENICE_CODE => [
            self::TS_AREA_LOC_TXT => self::TS_LOC_EUR,
            self::TS_AREA_CITY_TXT => 'VCE',
        ],
        self::TF_INTERLAKEN_CODE => [
            self::TS_AREA_LOC_TXT => self::TS_LOC_EUR,
            self::TS_AREA_CITY_TXT => 'ZIN',
        ],
        self::TF_MADRID_CODE => [
            self::TS_AREA_LOC_TXT => self::TS_LOC_EUR,
            self::TS_AREA_CITY_TXT => 'MAD',
        ],
        self::TF_BARCELONA_CODE => [
            self::TS_AREA_LOC_TXT => self::TS_LOC_EUR,
            self::TS_AREA_CITY_TXT => 'BCN',
        ],
        self::TF_VIENNA_CODE => [
            self::TS_AREA_LOC_TXT => self::TS_LOC_EUR,
            self::TS_AREA_CITY_TXT => 'VIE',
        ],
        self::TF_FRANKFURT_CODE => [
            self::TS_AREA_LOC_TXT => self::TS_LOC_EUR,
            self::TS_AREA_CITY_TXT => 'FRA',
        ],

        self::TF_PRAGUE_CODE => [
            self::TS_AREA_LOC_TXT => self::TS_LOC_EUR,
            self::TS_AREA_CITY_TXT => 'PRG',
        ],

        self::TF_ISTANBUL_CODE => [
            self::TS_AREA_LOC_TXT => self::TS_LOC_EUR,
            self::TS_AREA_CITY_TXT => 'IST',
        ],

        self::TF_NEWYORK_CODE => [
            self::TS_AREA_LOC_TXT => self::TS_LOC_AME,
            self::TS_AREA_CITY_TXT => 'NYC',
        ],
        self::TF_HAWAIIHONOLULU_CODE => [
            self::TS_AREA_LOC_TXT => self::TS_LOC_AME,
            self::TS_AREA_CITY_TXT => 'HNL',
        ],
        self::TF_LASVEGAS_CODE => [
            self::TS_AREA_LOC_TXT => self::TS_LOC_AME,
            self::TS_AREA_CITY_TXT => 'LAS',
        ],
        self::TF_LOSANGELES_CODE => [
            self::TS_AREA_LOC_TXT => self::TS_LOC_AME,
            self::TS_AREA_CITY_TXT => 'LAX',
        ],
        self::TF_SANFRANCISCO_CODE => [
            self::TS_AREA_LOC_TXT => self::TS_LOC_AME,
            self::TS_AREA_CITY_TXT => 'SFO',
        ],
        self::TF_NHATRANG_CODE => [
            self::TS_AREA_LOC_TXT => self::TS_LOC_ESA,
            self::TS_AREA_CITY_TXT => 'NHA',
        ],
        self::TF_PALAU_CODE => [
            self::TS_AREA_LOC_TXT => self::TS_LOC_ESA,
            self::TS_AREA_CITY_TXT => 'ROR',
        ],
        self::TF_HOIAN_CODE => [
            self::TS_AREA_LOC_TXT => self::TS_LOC_ESA,
            self::TS_AREA_CITY_TXT => 'GCE',
        ],
        self::TF_HUAHIN_CODE => [
            self::TS_AREA_LOC_TXT => self::TS_LOC_ESA,
            self::TS_AREA_CITY_TXT => 'HHQ',
        ],
        self::TF_MOSCOW_CODE => [
            self::TS_AREA_LOC_TXT => self::TS_LOC_EUR,
            self::TS_AREA_CITY_TXT => 'MOW',
        ],
        self::TF_VLADIVOSTOK_CODE => [
            self::TS_AREA_LOC_TXT => self::TS_LOC_EUR,
            self::TS_AREA_CITY_TXT => 'VVO',
        ],
        self::TF_MUINE_CODE => [
            self::TS_AREA_LOC_TXT => self::TS_LOC_EUR,
            self::TS_AREA_CITY_TXT => 'MIN',
        ],
        self::TF_SEVILLE_CODE => [
            self::TS_AREA_LOC_TXT => self::TS_LOC_EUR,
            self::TS_AREA_CITY_TXT => 'SVQ',
        ],
        self::TF_GRANADA_CODE => [
            self::TS_AREA_LOC_TXT => self::TS_LOC_EUR,
            self::TS_AREA_CITY_TXT => 'GRX',
        ],
        self::TF_MALAGA_CODE => [
            self::TS_AREA_LOC_TXT => self::TS_LOC_EUR,
            self::TS_AREA_CITY_TXT => 'AGP',
        ],
        self::TF_PHUQUOC_CODE => [
            self::TS_AREA_LOC_TXT => self::TS_LOC_EUR,
            self::TS_AREA_CITY_TXT => 'PQC',
        ],
        self::TF_DRESDEN_CODE => [
            self::TS_AREA_LOC_TXT => self::TS_LOC_EUR,
            self::TS_AREA_CITY_TXT => 'DRS',
        ],
        self::TF_HALLSTATT_CODE => [
            self::TS_AREA_LOC_TXT => self::TS_LOC_EUR,
            self::TS_AREA_CITY_TXT => 'GKF',
        ],
        self::TF_CESKYKRUMLOV_CODE => [
            self::TS_AREA_LOC_TXT => self::TS_LOC_EUR,
            self::TS_AREA_CITY_TXT => 'CKC',
        ],
        self::TF_ZAGREB_CODE => [
            self::TS_AREA_LOC_TXT => self::TS_LOC_EUR,
            self::TS_AREA_CITY_TXT => 'ZAG',
        ],
        self::TF_DUBROVNIK_CODE => [
            self::TS_AREA_LOC_TXT => self::TS_LOC_EUR,
            self::TS_AREA_CITY_TXT => 'DBV',
        ],
        self::TF_PLITVICE_CODE => [
            self::TS_AREA_LOC_TXT => self::TS_LOC_EUR,
            self::TS_AREA_CITY_TXT => 'ZAG',
        ],
        self::TF_LJUBLJANA_CODE => [
            self::TS_AREA_LOC_TXT => self::TS_LOC_EUR,
            self::TS_AREA_CITY_TXT => 'ZRH',
        ],
        self::TF_ZURICH_CODE => [
            self::TS_AREA_LOC_TXT => self::TS_LOC_EUR,
            self::TS_AREA_CITY_TXT => 'ZRH',
        ],
        self::TF_LISBON_CODE => [
            self::TS_AREA_LOC_TXT => self::TS_LOC_EUR,
            self::TS_AREA_CITY_TXT => 'LIS',
        ],
        self::TF_BUDAPEST_CODE => [
            self::TS_AREA_LOC_TXT => self::TS_LOC_EUR,
            self::TS_AREA_CITY_TXT => 'BUD',
        ],
        self::TF_SPLIT_CODE => [
            self::TS_AREA_LOC_TXT => self::TS_LOC_EUR,
            self::TS_AREA_CITY_TXT => 'ZAG',
        ],
        self::TF_BLED_CODE => [
            self::TS_AREA_LOC_TXT => self::TS_LOC_EUR,
            self::TS_AREA_CITY_TXT => 'LJU',
        ],
        self::TF_CAPPADOCIA_CODE => [
            self::TS_AREA_LOC_TXT => self::TS_LOC_EUR,
            self::TS_AREA_CITY_TXT => 'CPD',
        ],
        self::TF_AMALFI_CODE => [
            self::TS_AREA_LOC_TXT => self::TS_LOC_EUR,
            self::TS_AREA_CITY_TXT => 'AMF',
        ],

    ];


}