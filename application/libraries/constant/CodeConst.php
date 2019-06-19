<?php
namespace libraries\constant;

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class CodeConst
{

    /**
     * 노선 대분류 코드
     */
    const LINE_CODE_LOCAL = 'LOC'; // 국내
    const LINE_CODE_JAPAN = 'JPN'; // 일본
    const LINE_CODE_ASIAS = 'ASS'; // 아시아 중단거리
    const LINE_CODE_ASIAL = 'ASL'; // 아시아 장거리
    const LINE_CODE_OCEAN = 'OCN'; // 대양주/러시아

    /**
     * 노선 대분류 코드명
     */
    const LINE_CODES = [
        self::LINE_CODE_LOCAL => '국내',
        self::LINE_CODE_JAPAN => '아시아단거리(일본)',
        self::LINE_CODE_ASIAS => '아시아중단거리',
        self::LINE_CODE_ASIAL => '아시아장거리',
        self::LINE_CODE_OCEAN => '대양주/러시아',
    ];

    /**
     * 공항 코드
     */
    const AIRPORT_CODE_INCHEON      = 'ICN'; // 인천
    const AIRPORT_CODE_JEJU         = 'CJU'; // 제주
    const AIRPORT_CODE_GIMPO        = 'GMP'; // 김포
    const AIRPORT_CODE_DEAGU        = 'TAE'; // 대구
    const AIRPORT_CODE_GWANGU       = 'KWJ'; // 광주
    const AIRPORT_CODE_MOOAN        = 'MWX'; // 무안
    const AIRPORT_CODE_BUSAN        = 'PUS'; // 부산

    const AIRPORT_CODE_KUMAMOTO     = 'KMJ'; // 구마모토
    const AIRPORT_CODE_NAGOYA       = 'NGO'; // 나고야
    const AIRPORT_CODE_TOKYO        = 'NRT'; // 도쿄(나리타)
    const AIRPORT_CODE_SAGA         = 'HSG'; // 사가
    const AIRPORT_CODE_SAPORO       = 'CTS'; // 삿포로
    const AIRPORT_CODE_OSAKA        = 'KIX'; // 오사카(간사이)
    const AIRPORT_CODE_OITA         = 'OIT'; // 오이타
    const AIRPORT_CODE_OKINAWA      = 'OKA'; // 오키나와
    const AIRPORT_CODE_FUKUOKA      = 'FUK'; // 후쿠오카

    const AIRPORT_CODE_TAIPEI_SONG  = 'TSA'; // 타이베이 (송산)
    const AIRPORT_CODE_KAOHSIUNG    = 'KHH'; // 가오슝
    const AIRPORT_CODE_MACAU        = 'MFM'; // 마카오
    const AIRPORT_CODE_WENZHOU      = 'WNZ'; // 원저우
    const AIRPORT_CODE_JINAN        = 'TNA'; // 지난
    const AIRPORT_CODE_QINGDAO      = 'TAO'; // 칭다오
    const AIRPORT_CODE_TAIJOONG     = 'RMQ'; // 타이중
    const AIRPORT_CODE_TAIPEI_TAO   = 'TPE'; // 타이베이(타오위안)
    const AIRPORT_CODE_HONGKONG     = 'HKG'; // 홍콩
    const AIRPORT_CODE_NANNING      = 'NNG'; // 난닝

    const AIRPORT_CODE_DANANG       = 'DAD'; //다낭
    const AIRPORT_CODE_BANGKOK      = 'BKK'; //방콕(수완나폼)
    const AIRPORT_CODE_VIENTIAN     = 'VTE'; //비엔티안
    const AIRPORT_CODE_SANYA        = 'SYX'; //산야
    const AIRPORT_CODE_HAIKOU       = 'HAK'; //하이커우
    const AIRPORT_CODE_HOCHIMINH    = 'SGN'; //호찌민
    const AIRPORT_CODE_CEBU         = 'BKK'; //세부

    const AIRPORT_CODE_GUAM         = 'GUM'; // 괌
    const AIRPORT_CODE_SAIPAN       = 'SPN'; // 사이판
    const AIRPORT_CODE_VLADIVOSTOK  = 'VVO'; // 블라디보스토크
    const AIRPORT_CODE_KHABAROVSK   = 'KHV'; // 하바로브스크

    /**
     * 공항 코드명
     */
    const AIRPORT_CODE_NAME = [

        self::AIRPORT_CODE_INCHEON => '인천',
        self::AIRPORT_CODE_JEJU    => '제주',
        self::AIRPORT_CODE_GIMPO   => '김포',
        self::AIRPORT_CODE_DEAGU   => '대구',
        self::AIRPORT_CODE_GWANGU  => '광주',
        self::AIRPORT_CODE_MOOAN   => '무안',
        self::AIRPORT_CODE_BUSAN   => '부산',

        self::AIRPORT_CODE_KUMAMOTO => '구마모토',
        self::AIRPORT_CODE_NAGOYA   => '나고야',
        self::AIRPORT_CODE_TOKYO    => '도쿄(나리타)',
        self::AIRPORT_CODE_SAGA     => '사가',
        self::AIRPORT_CODE_SAPORO   => '삿포로',
        self::AIRPORT_CODE_OSAKA    => '오사카(간사이)',
        self::AIRPORT_CODE_OITA     => '오이타',
        self::AIRPORT_CODE_OKINAWA  => '오키나와',
        self::AIRPORT_CODE_FUKUOKA  => '후쿠오카',

        self::AIRPORT_CODE_TAIPEI_SONG => '타이베이 (송산)',
        self::AIRPORT_CODE_KAOHSIUNG   => '가오슝',
        self::AIRPORT_CODE_MACAU       => '마카오',
        self::AIRPORT_CODE_WENZHOU     => '원저우',
        self::AIRPORT_CODE_JINAN       => '지난',
        self::AIRPORT_CODE_QINGDAO     => '칭다오',
        self::AIRPORT_CODE_TAIJOONG    => '타이중',
        self::AIRPORT_CODE_TAIPEI_TAO  => '타이베이(타오위안)',
        self::AIRPORT_CODE_HONGKONG    => '홍콩',
        self::AIRPORT_CODE_NANNING     => '난닝',

        self::AIRPORT_CODE_DANANG    => '다낭',
        self::AIRPORT_CODE_BANGKOK   => '방콕(수완나폼)',
        self::AIRPORT_CODE_VIENTIAN  => '비엔티안',
        self::AIRPORT_CODE_SANYA     => '산야',
        self::AIRPORT_CODE_HAIKOU    => '하이커우',
        self::AIRPORT_CODE_HOCHIMINH => '호찌민',
        self::AIRPORT_CODE_CEBU      => '세부',

        self::AIRPORT_CODE_GUAM        => '괌',
        self::AIRPORT_CODE_SAIPAN      => '사이판',
        self::AIRPORT_CODE_VLADIVOSTOK => '블라디보스토크',
        self::AIRPORT_CODE_KHABAROVSK  => '하바로브스크',

    ];

    /**
     * 목적지 공항 코드 배열
     */
    const TWAY_DESTINATION_AIRPORT_CODES = [
        // 국내
        self::LINE_CODE_LOCAL => [
            self::AIRPORT_CODE_JEJU => [
                'depart_available' => [self::AIRPORT_CODE_GIMPO, self::AIRPORT_CODE_DEAGU, self::AIRPORT_CODE_GWANGU, self::AIRPORT_CODE_MOOAN]
            ],
            self::AIRPORT_CODE_GIMPO => [
                'depart_available' => [self::AIRPORT_CODE_JEJU]
            ],
            self::AIRPORT_CODE_DEAGU => [
                'depart_available' => [self::AIRPORT_CODE_JEJU]
            ],
            self::AIRPORT_CODE_GWANGU => [
                'depart_available' => [self::AIRPORT_CODE_JEJU]
            ],
            self::AIRPORT_CODE_MOOAN => [
                'depart_available' => [self::AIRPORT_CODE_JEJU]
            ],
        ],

        // 일본
        self::LINE_CODE_JAPAN => [

            self::AIRPORT_CODE_INCHEON => [
                'depart_available' => [
                    self::AIRPORT_CODE_KUMAMOTO,
                    self::AIRPORT_CODE_NAGOYA,
                    self::AIRPORT_CODE_TOKYO,
                    self::AIRPORT_CODE_SAGA,
                    self::AIRPORT_CODE_SAPORO,
                    self::AIRPORT_CODE_OSAKA,
                    self::AIRPORT_CODE_OITA,
                    self::AIRPORT_CODE_OKINAWA,
                    self::AIRPORT_CODE_FUKUOKA,
                ]
            ],
            self::AIRPORT_CODE_DEAGU => [
                'depart_available' => [
                    self::AIRPORT_CODE_TOKYO,
                    self::AIRPORT_CODE_OSAKA,
                    self::AIRPORT_CODE_OKINAWA,
                    self::AIRPORT_CODE_FUKUOKA,
                ]
            ],
            self::AIRPORT_CODE_JEJU => [
                'depart_available' => [
                    self::AIRPORT_CODE_TOKYO,
                    self::AIRPORT_CODE_OSAKA,
                ]
            ],
            self::AIRPORT_CODE_BUSAN => [
                'depart_available' => [self::AIRPORT_CODE_OSAKA]
            ],
            self::AIRPORT_CODE_KUMAMOTO => [
                'depart_available' => [self::AIRPORT_CODE_INCHEON]
            ],
            self::AIRPORT_CODE_NAGOYA => [
                'depart_available' => [self::AIRPORT_CODE_INCHEON]
            ],
            self::AIRPORT_CODE_TOKYO => [
                'depart_available' => [
                    self::AIRPORT_CODE_INCHEON,
                    self::AIRPORT_CODE_DEAGU
                ]
            ],
            self::AIRPORT_CODE_SAGA => [
                'depart_available' => [self::AIRPORT_CODE_INCHEON]
            ],
            self::AIRPORT_CODE_SAPORO => [
                'depart_available' => [self::AIRPORT_CODE_INCHEON]
            ],
            self::AIRPORT_CODE_OSAKA => [
                'depart_available' => [
                    self::AIRPORT_CODE_INCHEON,
                    self::AIRPORT_CODE_DEAGU,
                    self::AIRPORT_CODE_JEJU,
                    self::AIRPORT_CODE_BUSAN
                ]
            ],
            self::AIRPORT_CODE_OITA => [
                'depart_available' => [self::AIRPORT_CODE_INCHEON]
            ],
            self::AIRPORT_CODE_OKINAWA => [
                'depart_available' => [
                    self::AIRPORT_CODE_INCHEON,
                    self::AIRPORT_CODE_DEAGU
                ]
            ],
            self::AIRPORT_CODE_FUKUOKA => [
                'depart_available' => [
                    self::AIRPORT_CODE_INCHEON,
                    self::AIRPORT_CODE_DEAGU
                ]
            ],
        ],

        // 아시아 중단거리
        self::LINE_CODE_ASIAS => [
            self::AIRPORT_CODE_INCHEON => [
                'depart_available' => [
                    self::AIRPORT_CODE_KAOHSIUNG,
                    self::AIRPORT_CODE_MACAU,
                    self::AIRPORT_CODE_WENZHOU,
                    self::AIRPORT_CODE_JINAN,
                    self::AIRPORT_CODE_QINGDAO,
                    self::AIRPORT_CODE_TAIJOONG,
                ]
            ],
            self::AIRPORT_CODE_DEAGU => [
                'depart_available' => [
                    self::AIRPORT_CODE_TAIPEI_TAO,
                    self::AIRPORT_CODE_HONGKONG,
                ]
            ],
            self::AIRPORT_CODE_KAOHSIUNG => [
                'depart_available' => [self::AIRPORT_CODE_INCHEON]
            ],
            self::AIRPORT_CODE_MACAU => [
                'depart_available' => [self::AIRPORT_CODE_INCHEON]
            ],
            self::AIRPORT_CODE_WENZHOU => [
                'depart_available' => [self::AIRPORT_CODE_INCHEON]
            ],
            self::AIRPORT_CODE_JINAN => [
                'depart_available' => [self::AIRPORT_CODE_INCHEON]
            ],
            self::AIRPORT_CODE_QINGDAO => [
                'depart_available' => [self::AIRPORT_CODE_INCHEON]
            ],
            self::AIRPORT_CODE_TAIJOONG => [
                'depart_available' => [self::AIRPORT_CODE_INCHEON]
            ],
            self::AIRPORT_CODE_TAIPEI_SONG => [
                'depart_available' => [self::AIRPORT_CODE_GIMPO]
            ],
            self::AIRPORT_CODE_HONGKONG => [
                'depart_available' => [self::AIRPORT_CODE_DEAGU]
            ],
            self::AIRPORT_CODE_NANNING => [
                'depart_available' => [self::AIRPORT_CODE_JEJU]
            ],
            self::AIRPORT_CODE_TAIPEI_TAO => [
                'depart_available' => [
                    self::AIRPORT_CODE_DEAGU,
                    self::AIRPORT_CODE_BUSAN
                ]
            ],
        ],

        // 아시아 장거리
        self::LINE_CODE_ASIAL => [
            self::AIRPORT_CODE_INCHEON => [
                'depart_available' => [
                    self::AIRPORT_CODE_DANANG,
                    self::AIRPORT_CODE_BANGKOK,
                    self::AIRPORT_CODE_VIENTIAN,
                    self::AIRPORT_CODE_SANYA,
                    self::AIRPORT_CODE_HAIKOU,
                    self::AIRPORT_CODE_HOCHIMINH,
                ]
            ],
            self::AIRPORT_CODE_DEAGU => [
                'depart_available' => [
                    self::AIRPORT_CODE_DANANG,
                    self::AIRPORT_CODE_BANGKOK,
                    self::AIRPORT_CODE_CEBU,
                ]
            ],
            self::AIRPORT_CODE_BUSAN => [
                'depart_available' => [self::AIRPORT_CODE_DANANG]
            ],
            self::AIRPORT_CODE_DANANG => [
                'depart_available' => [
                    self::AIRPORT_CODE_INCHEON,
                    self::AIRPORT_CODE_DEAGU,
                    self::AIRPORT_CODE_BUSAN,
                ]
            ],
            self::AIRPORT_CODE_BANGKOK => [
                'depart_available' => [
                    self::AIRPORT_CODE_INCHEON,
                    self::AIRPORT_CODE_DEAGU,
                ]
            ],
            self::AIRPORT_CODE_VIENTIAN => [
                'depart_available' => [self::AIRPORT_CODE_INCHEON]
            ],
            self::AIRPORT_CODE_SANYA => [
                'depart_available' => [self::AIRPORT_CODE_INCHEON]
            ],
            self::AIRPORT_CODE_HAIKOU => [
                'depart_available' => [self::AIRPORT_CODE_INCHEON]
            ],
            self::AIRPORT_CODE_HOCHIMINH => [
                'depart_available' => [self::AIRPORT_CODE_INCHEON]
            ],
        ],

        // 대양주/러시아
        self::LINE_CODE_OCEAN => [
            self::AIRPORT_CODE_INCHEON => [
                'depart_available' => [
                    self::AIRPORT_CODE_GUAM,
                    self::AIRPORT_CODE_SAIPAN,
                ]
            ],
            self::AIRPORT_CODE_DEAGU => [
                'depart_available' => [
                    self::AIRPORT_CODE_GUAM,
                    self::AIRPORT_CODE_VLADIVOSTOK,
                    self::AIRPORT_CODE_KHABAROVSK,
                ]
            ],
            self::AIRPORT_CODE_GUAM => [
                'depart_available' => [
                    self::AIRPORT_CODE_OSAKA,
                    self::AIRPORT_CODE_INCHEON,
                    self::AIRPORT_CODE_DEAGU,
                ]
            ],
            self::AIRPORT_CODE_SAIPAN => [
                'depart_available' => [self::AIRPORT_CODE_INCHEON]
            ],
            self::AIRPORT_CODE_VLADIVOSTOK => [
                'depart_available' => [self::AIRPORT_CODE_DEAGU]
            ],
            self::AIRPORT_CODE_KHABAROVSK => [
                'depart_available' => [self::AIRPORT_CODE_DEAGU]
            ],
        ],
    ];


    const TWAY_TRIP_TYPE_ONEWAY     = 'OW';
    const TWAY_TRIP_TYPE_ROUND_TRIP = 'RT';
    const TWAY_TRIP_TYPE_MULTICITY  = 'MC';

    /**
     *
     */
    const TWAY_TRIP_TYPES = [
        self::TWAY_TRIP_TYPE_ONEWAY     => '편도',
        self::TWAY_TRIP_TYPE_ROUND_TRIP => '왕복',
        self::TWAY_TRIP_TYPE_MULTICITY  => 'MultiCity',
    ];

    const TWAY_PAX_TYPE_ADULT  = 'ADULT';
    const TWAY_PAX_TYPE_CHILD  = 'CHILD';
    const TWAY_PAX_TYPE_INFANT = 'INFANT';

    const TWAY_PAX_TYPES = [
        self::TWAY_PAX_TYPE_ADULT  => '성인',
        self::TWAY_PAX_TYPE_CHILD  => '소아',
        self::TWAY_PAX_TYPE_INFANT => '유아',
    ];


}