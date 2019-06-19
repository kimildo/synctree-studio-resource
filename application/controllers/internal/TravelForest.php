<?php

    /**
     * TF 용 커스텀 컨트롤러
     *
     */

    namespace controllers\internal;

    use Slim\Http\Request;
    use Slim\Http\Response;
    use Psr\Container\ContainerInterface;

    use libraries\{
        log\LogMessage,
        constant\CommonConst,
        constant\ErrorConst,
        constant\TFConst,
        util\CommonUtil,
        util\RedisUtil,
        util\AwsUtil,
        http\Http
    };

    class TravelForest extends SynctreeInternal
    {

        private $guzzleOpt = [
                'verify'          => false,
                'timeout'         => CommonConst::HTTP_RESPONSE_TIMEOUT,
                'connect_timeout' => CommonConst::HTTP_CONNECT_TIMEOUT
            ];

        private $noWait = true;
        private $maxTryCount = 10;
        private $redisSession = TFConst::REDIS_TF_SESSION;
        private $recipients = [];

        public function __construct(ContainerInterface $ci)
        {
            parent::__construct($ci);

            $this->recipients[] = 'kimildo78@nntuple.com';
            if (APP_ENV === APP_ENV_STAGING || APP_ENV === APP_ENV_PRODUCTION) {
                $this->recipients[] = 'info@travelforest.co.kr';
            }
        }

        public function template(Request $request, Response $response)
        {
            $results = $this->jsonResult;

            try {


            } catch (\Exception $ex) {

                $results = [
                    'result' => false,
                    'data'   => [
                        'message' => $this->_getErrorMessage($ex),
                    ]
                ];
            }

            return $response->withJson($results, ErrorConst::SUCCESS_CODE);

        }


        /**
         * 모상품 목록 가지고 오기
         *
         * @param Request  $request
         * @param Response $response
         *
         * @return Response
         */
        public function getProducts(Request $request, Response $response)
        {
            $products = null;

            try {

                if (false === ($products = RedisUtil::getData($this->redis, TFConst::REDIS_TF_PRODUCTS_REDIS_KEY, $this->redisSession))) {
                    throw new \Exception('', ErrorConst::ERROR_RDB_NO_DATA_EXIST);
                }

                $results = $products;

            } catch (\Exception $ex) {
                $results = [
                    'result' => ErrorConst::FAIL_STRING,
                    'data'   => [
                        'message' => $this->_getErrorMessage($ex),
                    ]
                ];
            }

            return $response->withJson($results, ErrorConst::SUCCESS_CODE);
        }


        /**
         * 자상품의 번호로 모상품의 IDX를 리턴
         *
         * @param Request  $request
         * @param Response $response
         * @param          $args
         *
         * @return Response
         */
        public function getProductIdx(Request $request, Response $response, $args)
        {
            $productIdx = null;

            try {

                $params = $args;

                if (false === CommonUtil::validateParams($params, ['productTypeKey'])) {
                    throw new \Exception(null, ErrorConst::ERROR_NOT_FOUND_REQUIRE_FIELD);
                }

                $redisKey = TFConst::REDIS_TF_SUB_PRODUCT_REDIS_KEY . '_' . $params['productTypeKey'];
                if (false === ($product = RedisUtil::getData($this->redis, $redisKey, $this->redisSession))) {
                    throw new \Exception('', ErrorConst::ERROR_RDB_NO_DATA_EXIST);
                }

                $productIdx = $product['product_idx'];

                $results = [
                    'productKey' => $productIdx
                ];

            } catch (\Exception $ex) {
                $results = [
                    'result' => ErrorConst::FAIL_STRING,
                    'data'   => [
                        'message' => $this->_getErrorMessage($ex),
                    ]
                ];
            }

            return $response->withJson($results, ErrorConst::SUCCESS_CODE);


        }

        /**
         * 모상품의 상세정보 리턴
         *
         * @param Request  $request
         * @param Response $response
         *
         * @return Response
         */
        public function getProduct(Request $request, Response $response)
        {
            $productDetail = [];

            try {

                $params = $request->getAttribute('params');

                if (false === CommonUtil::validateParams($params, ['product'], true)) {
                    throw new \Exception(null, ErrorConst::ERROR_NOT_FOUND_REQUIRE_FIELD);
                }

                $product = $params['product'];
                $dateSearchFlag = false;

                if ( ! is_array($product) || empty($product) ) {
                    throw new \Exception(null, ErrorConst::ERROR_NOT_FOUND_REQUIRE_FIELD);
                }

                $tourOptions = [];
                $priceArr = [];
                $priceAdultArr = [];
                $priceChildArr = [];
                $paxArr = [];
                $saleAvailableTour = [];
                $pDate = [];

                if (isset($params['fromDate']) && isset($params['toDate']) && ! empty($params['fromDate']) && ! empty($params['toDate'])) {

                    $dateSearchFlag = true;

                    $fromDate = date('Y-m-d', strtotime($params['fromDate']));
                    $toDate = date('Y-m-d', strtotime($params['toDate']));

                    $fromDateTime = new \DateTime($fromDate);
                    $toDateTime = new \DateTime($toDate);

                    $dateDiff = date_diff($fromDateTime, $toDateTime);
                    $dateDiff = $dateDiff->days + 1;

                    if ($dateDiff > 366) {
                        throw new \Exception('', ErrorConst::ERROR_RDB_NO_DATA_EXIST);
                    }

                    $period = new \DatePeriod($fromDateTime, new \DateInterval('P1D'), $toDateTime->modify('+1 day'));

                    foreach ($period as $key => $value) {
                        $pDate[] = $value->format('Y-m-d');
                    }

                }

                switch (true) {

                    // 자상품 상세
                    case (isset($params['productTypeKey']) && ! empty($params['productTypeKey']) && $params['productTypeKey'] !== 'null') :

                        if ( ! isset($params['productTypes']) || empty($params['productTypes'])) {
                            break;
                        }

                        $voucherUse = null;
                        $refundPolicy = null;
                        $tourItemFlag = false;
                        $transferItemFlag = false;

                        // 투어 상품과 픽업 상품이 나뉨
                        switch (true) {
                            case (isset($product['tour_item']) && is_array($product['tour_item'])) :
                                $tourItemFlag = true;
                                break;
                            case (isset($product['tour_items']) && is_array($product['tour_items'])) :
                                if (false === ($tourIdx = array_search($params['productTypeKey'],
                                        array_column($product['tour_items'], 'tour_item_idx')))
                                ) {
                                    throw new \Exception('', ErrorConst::ERROR_RDB_NO_DATA_EXIST);
                                }
                                $tourItemFlag = true;
                                $product['tour_item'] = $product['tour_items'][$tourIdx];
                                break;
                            case (isset($product['transfer_item']) && is_array($product['transfer_item'])) :
                                $transferItemFlag = true;
                                break;
                            case (isset($product['transfer_items']) && is_array($product['transfer_items'])) :
                                if (false === ($tourIdx = array_search($params['productTypeKey'],
                                        array_column($product['transfer_items'], 'transfer_item_idx')))
                                ) {
                                    throw new \Exception('', ErrorConst::ERROR_RDB_NO_DATA_EXIST);
                                }
                                $transferItemFlag = true;
                                $product['transfer_item'] = $product['transfer_items'][$tourIdx];
                                break;
                        }

                        if (true === $tourItemFlag) { // 투어 아이템일 경우

                            $tourItem = $product['tour_item'];

                            $productDetail['productTypeKey'] = $tourItem['tour_item_idx'];

                            $productDetail['title'] = $tourItem['tour_ko'];
                            $productDetail['title'] = str_replace(['&gt;', '&lt;'], ['>','<'], $productDetail['title']);

                            $productDetail['durationDays'] = (int)$tourItem['duration_day'] ?? 0;
                            $productDetail['durationHours'] = (int)$tourItem['duration_hour'] ?? 0;
                            $productDetail['durationMins'] = (int)$tourItem['duration_minute'] ?? 0;
                            $productDetail['minAdultAge'] = 0;
                            $productDetail['maxAdultAge'] = 99;
                            $productDetail['hasChildPrice'] = false;
                            $productDetail['minChildAge'] = 0;

                            if (isset($tourItem['tour_suppliers'])) {
                                foreach ($tourItem['tour_suppliers'] as $tsp) {

                                    // 바우처 정보
                                    if (isset($tsp['voucher_content']) && ! empty($tsp['voucher_content'])) {
                                        $voucherUse .= $tsp['voucher_content'];
                                    };

                                    // 환불규정
                                    $refundPolicy = $tsp['cancel_content'] ?? '';

                                    // 최대/최소 가격 , 최소/최대 여행자수
                                    foreach ($tsp['tour_seasons'] as $ts) {

                                        // 날짜 검색이면
                                        if (true === $dateSearchFlag) {

                                            if ( ! empty($pDate)) {

                                                $tourDateFrom = date('Y-m-d', strtotime($ts['season_date_from']));
                                                $tourDateTo = date('Y-m-d', strtotime($ts['season_date_to']));
                                                $tourSaleDateTo = date('Y-m-d',
                                                    strtotime($ts['season_date_to'] . ' -' . $ts['sale_day_to'] . ' days'));

                                                foreach ($pDate as $sDate) {
                                                    $tmpDate = date('Y-m-d', strtotime($sDate));
                                                    if ($tmpDate >= $tourDateFrom && $tmpDate <= $tourDateTo) {
                                                        $saleAvailableTour[] = [
                                                            'tour_season_idx' => $ts['tour_season_idx'],
                                                            'from_to'         => $tmpDate,
                                                        ];
                                                    }
                                                }
                                            }
                                        }

                                        // 가격/여행객 수
                                        foreach ($ts['tour_prices'] as $tps) {

                                            if ($tps['sale_unit_ko'] === '성인') {
                                                $productDetail['minAdultAge'] = (int)$tps['customer_age_from'];
                                                $productDetail['maxAdultAge'] = (int)$tps['customer_age_to'];
                                                $priceArr[] = $tps['sale_price'];
                                                $priceAdultArr[] = $tps['sale_price'];
                                            } elseif ($tps['sale_unit_ko'] === '아동') {
                                                $productDetail['hasChildPrice'] = true;
                                                $productDetail['minChildAge'] = (int)$tps['customer_age_from'];
                                                $priceChildArr[] = $tps['sale_price'];
                                            }

                                            if ( ! empty($tps['customer_count_from'])) {
                                                $paxArr[] = (int)$tps['customer_count_from'];
                                            }

                                            if ( ! empty($tps['customer_count_from'])) {
                                                $paxArr[] = (int)$tps['customer_count_to'];
                                            }

                                        }

                                    }

                                    // 자상품 옵션
                                    if (isset($tsp['tour_options']) && ! empty($tsp['tour_options'])) {
                                        foreach ($tsp['tour_options'] as $to) {

                                            if ( ! isset($to['tour_fields']) || empty($to['tour_fields'])) {
                                                continue;
                                            }

                                            foreach ($to['tour_fields'] as $tf) {

                                                $tmpOpt = [];
                                                $tmpOpt['id'] = $tf['field_id'];
                                                $tmpOpt['target'] = 1;
                                                $tmpOpt['name'] = $tf['field_label'];
                                                $tmpOpt['type'] = (isset($tf['field_items']) && ! empty($tf['field_items'])) ? 1 : 4;
                                                $tmpOpt['required'] = (isset($tf['field_items']) && ! empty($tf['field_items'])) ? true : false;
                                                $tmpOpt['price'] = 0;

                                                if (isset($tf['field_items']) && ! empty($tf['field_items']) && is_array($tf['field_items'])) {

                                                    $tmpOpt['item'] = [];
                                                    foreach ($tf['field_items'] as $fl) {
                                                        $tmpOpt['item'][] = [
                                                            'label' => $fl['item_text'],
                                                            'value' => $fl['item_value'],
                                                        ];
                                                    }
                                                }

                                                $tourOptions[] = $tmpOpt;
                                            }
                                        }
                                    }
                                }
                            }

                            $productDetail['minPax'] = ( ! empty($paxArr)) ? min($paxArr) : 0;
                            $productDetail['maxPax'] = ( ! empty($paxArr)) ? max($paxArr) : 0;

                        } elseif (true === $transferItemFlag) {

                            $tourItem = $product['transfer_item'];

                            $productDetail['productTypeKey'] = $tourItem['transfer_item_idx'];

                            $productDetail['title'] = $tourItem['transfer_ko'];
                            $productDetail['title'] = str_replace(['&gt;', '&lt;'], ['>','<'], $productDetail['title']);

                            $productDetail['durationDays'] = 0;
                            $productDetail['durationHours'] = 0;
                            $productDetail['durationMins'] = 0;

                            $productDetail['minAdultAge'] = 0;
                            $productDetail['maxAdultAge'] = 99;
                            $productDetail['hasChildPrice'] = false;
                            $productDetail['minChildAge'] = 0;

                            $priceChildArr = [0];

                            if (isset($tourItem['transfer_suppliers'])) {
                                foreach ($tourItem['transfer_suppliers'] as $tsp) {

                                    // 바우처 정보
                                    if (isset($tsp['voucher_content']) && ! empty($tsp['voucher_content'])) {
                                        $voucherUse .= ($tsp['note_content'] ?? null);
                                        $voucherUse .= ($tsp['voucher_content'] ?? null);
                                    };

                                    // 환불규정
                                    $refundPolicy = $tsp['cancel_content'] ?? '';

                                    // 최대/최소 가격
                                    foreach ($tsp['transfer_seasons'] as $ts) {

                                        // 날짜 검색이면
                                        if (true === $dateSearchFlag) {
                                            if ( ! empty($pDate)) {
                                                $tourDateFrom = date('Y-m-d', strtotime($ts['season_date_from']));
                                                $tourDateTo = date('Y-m-d', strtotime($ts['season_date_to']));
                                                $tourSaleDateTo = date('Y-m-d',
                                                    strtotime($ts['season_date_to'] . ' -' . $ts['sale_day_to'] . ' days'));

                                                foreach ($pDate as $sDate) {
                                                    $tmpDate = date('Y-m-d', strtotime($sDate));
                                                    if ($tmpDate >= $tourDateFrom && $tmpDate <= $tourDateTo) {
                                                        $saleAvailableTour[] = [
                                                            'from_to' => $tmpDate,
                                                        ];

                                                        foreach ($ts['transfer_prices'] as $tps) {
                                                            $priceArr[] = $priceAdultArr[] = $tps['sale_price'];

                                                            // 픽업 서비스인 경우 옵션에 상품정보를 넣는다.
                                                            $tourOptions[] = [
                                                                'id'       => $tps['transfer_price_idx'],
                                                                'target'   => 1,
                                                                'name'     => $tps['vehicle_type_ko'] . '-' . $tps['customer_count_to'] . '인',
                                                                'type'     => 3,
                                                                'required' => true,
                                                                'price'    => (int)$tps['sale_price']
                                                            ];
                                                        }
                                                    }
                                                }
                                            }

                                        } else {
                                            foreach ($ts['transfer_prices'] as $tps) {
                                                $priceArr[] = $priceAdultArr[] = $tps['sale_price'];

                                                // 픽업 서비스인 경우 옵션에 상품정보를 넣는다.
                                                $tourOptions[] = [
                                                    'id'       => $tps['transfer_price_idx'],
                                                    'target'   => 1,
                                                    'name'     => $tps['vehicle_type_ko'] . '-' . $tps['customer_count_to'] . '인',
                                                    'type'     => 3,
                                                    'required' => true,
                                                    'price'    => (int)$tps['sale_price']
                                                ];
                                            }
                                        }

                                    }

                                    // 자상품 옵션
                                    if (isset($tsp['transfer_option']['transfer_fields']) && ! empty($tsp['transfer_option']['transfer_fields'])) {

                                        $tFields = $tsp['transfer_option']['transfer_fields'];

                                        foreach ($tFields as $tf) {

                                            $tmpOpt = [];
                                            $tmpOpt['id'] = $tf['field_id'];
                                            $tmpOpt['target'] = 1;
                                            $tmpOpt['name'] = $tf['field_label'];
                                            $tmpOpt['type'] = (isset($tf['field_items']) && ! empty($tf['field_items'])) ? 1 : 4;
                                            $tmpOpt['required'] = true;
                                            $tmpOpt['price'] = 0;

                                            if (isset($tf['field_items']) && ! empty($tf['field_items']) && is_array($tf['field_items'])) {
                                                $tmpOpt['item'] = [];
                                                foreach ($tf['field_items'] as $fl) {
                                                    $tmpOpt['item'][] = [
                                                        'label' => $fl['item_text'] ?? '',
                                                        'value' => $fl['item_value'] ?? '',
                                                    ];
                                                }
                                            }

                                            $tourOptions[] = $tmpOpt;
                                        }
                                    }
                                }
                            }
                        }

                        $tourOptions = array_merge($tourOptions, [
                            [
                                'id' => 'arrival_date',
                                'target' => 1,
                                'name' => '현지 도착일',
                                'type' => 6,
                                'required' => true,
                                'price' => 0
                            ],
                            [
                                'id' => 'arrival_time',
                                'target' => 1,
                                'name' => '현지 도착시간',
                                'type' => 10,
                                'required' => true,
                                'price' => 0
                            ],
                            [
                                'id' => 'duration_date',
                                'target' => 1,
                                'name' => '여행일수',
                                'type' => 3,
                                'required' => true,
                                'price' => 0
                            ],
                            [
                                'id' => 'participant_sex',
                                'target' => 2,
                                'name' => '성별',
                                'type' => 1,
                                'required' => true,
                                'price' => 0,
                                'item' => [
                                    [
                                        'label' => 'Male',
                                        'value' => 'Male'
                                    ],
                                    [
                                        'label' => 'Female',
                                        'value' => 'Female'
                                    ]
                                ]
                            ],
                            [
                                'id' => 'participant_name',
                                'target' => 2,
                                'name' => '이름',
                                'type' => 4,
                                'required' => true,
                                'price' => 0
                            ],
                            [
                                'id' => 'participant_birth',
                                'target' => 2,
                                'name' => '생년월일',
                                'type' => 6,
                                'required' => true,
                                'price' => 0
                            ],
                            [
                                'id' => 'participant_nation',
                                'target' => 2,
                                'name' => '국가',
                                'type' => 4,
                                'required' => false,
                                'price' => 0
                            ],
                        ]);

                        $productDetail['hasSeniorPrice'] = false;
                        $productDetail['hasTeenagerPrice'] = false;
                        $productDetail['minTeenagerAge'] = 0;
                        $productDetail['allowInfants'] = false;
                        $productDetail['maxInfantAge'] = 0;
                        $productDetail['instantConfirmation'] = false;
                        $productDetail['voucherType'] = 'E_VOUCHER';
                        $productDetail['voucherUse'] = $voucherUse;
                        //$productDetail['meetingTime'] = $tourItem[''];
                        //$productDetail['meetingLocation'] = $tourItem[''];
                        $productDetail['isNonRefundable'] = true;
                        $productDetail['refundPolicy'] = $refundPolicy;
                        //$productDetail['validityType'] = $tourItem[''];
                        //$productDetail['validityDate'] = $tourItem[''];
                        $productDetail['minPrice'] = ( ! empty($priceArr)) ? (int)min($priceArr) : 0;
                        $productDetail['options'] = $tourOptions;

                        // 날짜검색이라면 데이터 초기화
                        if (true === $dateSearchFlag) {
                            $productDetail = [];
                        }

                        if (empty($priceAdultArr)) {
                            $priceAdultArr = [0];
                        }
                        if (empty($priceChildArr)) {
                            $priceChildArr = [0];
                        }

                        // 검색 날짜내에 가능한 상품이 있으면
                        if ( ! empty($saleAvailableTour)) {
                            foreach ($saleAvailableTour as $st) {
                                $productDetail[] = [
                                    'date'      => $st['from_to'],
                                    'available' => true,
                                    'price'     => [
                                        'adult'    => (int)max($priceAdultArr),
                                        'child'    => (int)max($priceChildArr),
                                        'senior'   => 0,
                                        'teenager' => 0,
                                    ]
                                ];
                            }
                        }

                        break;


                    // 자상품 리스트 반환
                    case (isset($params['productTypes']) && ! empty($params['productTypes']) && $params['productTypes'] !== 'null') :

                        if ( ! is_array($params['product'])) {
                            throw new \Exception(null, ErrorConst::ERROR_NOT_FOUND_REQUIRE_FIELD);
                        }

                        if (isset($product['tour_items']) && is_array($product['tour_items'])) {
                            foreach ($product['tour_items'] as $row) {
                                $productDetail[] = [
                                    'productTypeKey' => $row['tour_item_idx'],
                                    'title'          => str_replace(['&gt;', '&lt;'], ['>','<'], $row['tour_ko']),
                                ];
                            }
                        } elseif (isset($product['transfer_items']) && is_array($product['transfer_items'])) {
                            foreach ($product['transfer_items'] as $row) {
                                $productDetail[] = [
                                    'productTypeKey' => $row['transfer_item_idx'],
                                    'title'          => str_replace(['&gt;', '&lt;'], ['>','<'], $row['transfer_ko']),
                                ];
                            }
                        }

                        break;

                    // 모상품 상세
                    default :

                        if ( ! is_array($params['product']) || empty($product) ) {
                            throw new \Exception(null, ErrorConst::ERROR_NOT_FOUND_REQUIRE_FIELD);
                        }

                        $desc = ( ! empty($product['comment_content'])) ? $product['comment_content'] : ($product['description_content'] ?? null);
                        $desc = strip_tags($desc);

                        $productTitle = $product['product_ko'] ?? '';
                        $productDetail = [
                            'productKey'     => $product['product_idx'],
                            'updatedAt'      => $product['modify_datetime'] ?? null,
                            'title'          => str_replace(['&gt;', '&lt;'], ['>','<'], $productTitle),
                            'description'    => $desc ?? null,
                            'highlights'     => $product['highlight_content'] ?? null,
                            'additionalInfo' => strip_tags($product['comment_content'] ?? null) ?? null,
                        ];

                        // 여정
                        $productDetail['itinerary'] = '';
                        if ( ! empty($product['tour_itineraries']) && is_array($product['tour_itineraries'])) {
                            foreach ($product['tour_itineraries'] as $ti) {
                                $productDetail['itinerary'] .= ($ti['itinerary_daytime'] ?? null) . PHP_EOL;
                                $productDetail['itinerary'] .= ($ti['itinerary_route'] ?? null) . PHP_EOL;
                                $productDetail['itinerary'] .= ($ti['itinerary_content'] ?? null) . PHP_EOL . PHP_EOL;
                            }

                            $productDetail['itinerary'] = strip_tags($productDetail['itinerary']);
                        }

                        $productDetail['latitude'] = 0;
                        $productDetail['longitude'] = 0;
                        if (isset($product['tour_maps'][0]) && ! empty($product['tour_maps'][0])) {
                            $productDetail['latitude'] = (float)$product['tour_maps'][0]['point_latitude'];
                            $productDetail['longitude'] = (float)$product['tour_maps'][0]['point_longitude'];
                        }


                        $productDetail['currency'] = 'KRW';

                        // 여행상품 최소가
                        $priceArr = [];
                        if ( ! empty($product['tour_items']) && is_array($product['tour_items'])) {
                            foreach ($product['tour_items'] as $ti) {

                                if ( ! isset($ti['tour_suppliers']) || empty($ti['tour_suppliers'])) {
                                    continue;
                                }

                                foreach ($ti['tour_suppliers'] as $tsp) {
                                    foreach ($tsp['tour_seasons'] as $ts) {
                                        foreach ($ts['tour_prices'] as $tps) {
                                            $priceArr[] = $tps['sale_price'];
                                        }
                                    }
                                }
                            }
                        }

                        // 픽업상품 최소가
                        if ( ! empty($product['transfer_items']) && is_array($product['transfer_items'])) {
                            foreach ($product['transfer_items'] as $ti) {

                                if ( ! isset($ti['transfer_suppliers']) || empty($ti['transfer_suppliers'])) {
                                    continue;
                                }

                                foreach ($ti['transfer_suppliers'] as $tsp) {
                                    foreach ($tsp['transfer_seasons'] as $ts) {
                                        foreach ($ts['transfer_prices'] as $tps) {
                                            $priceArr[] = $tps['sale_price'];
                                        }
                                    }
                                }
                            }
                        }

                        $productDetail['minPrice'] = ( ! empty($priceArr)) ? ((int)min($priceArr)) : 0;

                        $redisKey = TFConst::REDIS_TF_PRODUCT_REDIS_KEY . '_' . $product['product_idx'];
                        if (false !== ($productRedis = RedisUtil::getData($this->redis, $redisKey, $this->redisSession))) {
                            $productDetail['location'] = TFConst::TS_AREA_CODE[$productRedis['area_idx']][TFConst::TS_AREA_LOC_TXT];
                            $productDetail['city'] = TFConst::TS_AREA_CODE[$productRedis['area_idx']][TFConst::TS_AREA_CITY_TXT];
                        }

                        $productDetail['photos'] = [];
                        if (isset($product['image_urls']) && ! empty($product['image_urls'])) {
                            foreach ($product['image_urls'] as $iu) {
                                $productDetail['photos'][] = ['path' => $iu];
                            }
                        }

                }

                $results = $productDetail;


            } catch (\Exception $ex) {
                $results = [
                    'result' => ErrorConst::FAIL_STRING,
                    'data'   => [
                        'message' => $this->_getErrorMessage($ex),
                    ]
                ];
            }

            return $response->withJson($results, ErrorConst::SUCCESS_CODE);

        }


        /**
         * TF 상품 배치
         * Redis에 무조건 덮어쓴다.
         *
         * @param Request  $request
         * @param Response $response
         *
         * @return Response
         * @throws \GuzzleHttp\Exception\GuzzleException
         */
        public function setProductsBatch(Request $request, Response $response)
        {
            $products = null;
            $productDetail = [];

            $startDate = CommonUtil::getDateTime();
            $startTime = CommonUtil::getMicroTime();
            $startTimeStemp = $startDate . ' ' . $startTime;
            $updateWebHook = 'http://tnaapi.tourvis.com/apiary/common/webhook/v1/product';
            $updatedParentProduct = [];
            $updatedChildProduct = [];

            try {

                // 지역 정보
                $redisKey = TFConst::REDIS_TF_AREA_REDIS_KEY;
                $targetUrl = TFConst::TF_URL . TFConst::TF_GET_AREA_URI;
                $area = Http::httpRequest($targetUrl, $this->guzzleOpt);
                $this->_setRedis($redisKey, $area);

                foreach ($area as $row) {

                    // 지역별 모상품 리스트
                    $areaIdx = $row['area_idx'];
                    $redisKey = TFConst::REDIS_TF_PRODUCTS_REDIS_KEY . '_' . $areaIdx;

                    LogMessage::info('TF area_idx :: ' . $areaIdx);
                    $targetUrl = TFConst::TF_URL . TFConst::TF_GET_AREA_URI . '/' . $areaIdx . '/products';
                    $areaProducts = Http::httpRequest($targetUrl, $this->guzzleOpt);
                    $this->_setRedis($redisKey, $areaProducts);

                    try {

                        // 지역별 모상품 리스트 루프
                        foreach ($areaProducts as $aps) {

                            $productIdx = $aps['product_idx'];
                            $updateFlag = false;

                            // 모상품 상세정보
                            LogMessage::info('TF product_idx :: ' . $productIdx);

                            $redisKey = TFConst::REDIS_TF_PRODUCT_REDIS_KEY . '_' . $productIdx;
                            $targetUrl = TFConst::TF_URL . TFConst::TF_GET_PRODUCTS_URI . '/' . $productIdx;
                            $productDetail = Http::httpRequest($targetUrl, $this->guzzleOpt);
                            $productDetail['area_idx'] = $areaIdx;

                            if (false !== ($productDetailOld = RedisUtil::getData($this->redis, $redisKey, $this->redisSession))) {
                                if ($productDetailOld['modify_datetime'] <> $productDetail['modify_datetime']) {
                                    $updateFlag = true;
                                    $updatedParentProduct[] = [
                                        'productKey' => $productIdx,
                                        'updateAt'   => $productDetail['modify_datetime'],
                                    ];
                                }
                            }

                            $this->_setRedis($redisKey, $productDetail);

                            try {

                                $tourItemIdx = 0;
                                $productSubDetail = null;

                                // 투어상품 상세정보
                                if (isset($productDetail['tour_items']) && is_array($productDetail['tour_items'])) {
                                    foreach ($productDetail['tour_items'] as $ti) {

                                        $tourItemIdx = $ti['tour_item_idx'];
                                        LogMessage::info('TF tour_item_idx :: ' . $tourItemIdx);

                                        $productSubDetail = [
                                            'area_idx'    => $areaIdx,
                                            'product_idx' => $productIdx,
                                            'tour_item'   => $ti
                                        ];

                                        $redisKey = TFConst::REDIS_TF_SUB_PRODUCT_REDIS_KEY . '_' . $tourItemIdx;
                                        $this->_setRedis($redisKey, $productSubDetail);
                                    }
                                }

                                // 픽업상품 상세정보
                                if (isset($productDetail['transfer_items']) && is_array($productDetail['transfer_items'])) {
                                    foreach ($productDetail['transfer_items'] as $ti) {

                                        $tourItemIdx = $ti['transfer_item_idx'];
                                        LogMessage::info('TF transfer_item_idx :: ' . $tourItemIdx);

                                        $productSubDetail = [
                                            'area_idx'      => $areaIdx,
                                            'product_idx'   => $productIdx,
                                            'transfer_item' => $ti
                                        ];

                                        $redisKey = TFConst::REDIS_TF_SUB_PRODUCT_REDIS_KEY . '_' . $tourItemIdx;
                                        $this->_setRedis($redisKey, $productSubDetail);
                                    }
                                }

                                if ($updateFlag === true) {
                                    $updatedChildProduct[$productDetail['product_idx']][] = $tourItemIdx;
                                }

                            } catch (\Exception $ee) {
                                LogMessage::error('SubProduct Detail Error :: ' . $areaIdx . '-' . $productIdx . '-' . $tourItemIdx);
                                LogMessage::error($ee->getMessage());
                                LogMessage::info(json_encode($tourItemIdx, JSON_UNESCAPED_UNICODE));
                            }

                            $products[] = [
                                'productKey' => $aps['product_idx'],
                                'updatedAt'  => $productDetail['modify_datetime'] ?? null,
                                'title'      => $aps['product_ko']
                            ];

                            sleep(1);

                        }

                    } catch (\Exception $e) {

                        LogMessage::error('Product Detail Error :: ' . $productIdx . '_' . $areaIdx);
                        LogMessage::error($e->getMessage());
                        LogMessage::info(json_encode($productDetail, JSON_UNESCAPED_UNICODE));

                    }

                    sleep(1);

                }

                $this->_setRedis(TFConst::REDIS_TF_PRODUCTS_REDIS_KEY, $products);
                $results = $products;

            } catch (\Exception $ex) {
                $results = [
                    'result' => ErrorConst::FAIL_STRING,
                    'data'   => [
                        'message' => $this->_getErrorMessage($ex),
                    ]
                ];
            }

// @todo
//        if (!empty($updatedParentProduct)) {
//            try {
//
//                foreach ($updatedParentProduct as $key => $pidx) {
//                    try {
//
//                        $data = [
//                            'productKey' => $pidx['productKey'],
//                            'updateType' => 'UPDATE',
//                            'updateAt' => $pidx['updateAt'],
//                        ];
//
//                        $command = 'curl -X ';
//                        $command .= '-d "' . http_build_query($data) . '" ';
//
//                        $command .= $updateWebHook . ' -s > /dev/null 2>&1 &';
//                        passthru($command);
//
//                        foreach ($updatedChildProduct[$pidx['productKey']] as $cidx) {
//                            try {
//                                $data = [
//                                    'productKey'     => $pidx['productKey'],
//                                    'productTypeKey' => $cidx,
//                                    'updateType'     => 'UPDATE',
//                                    'updateAt'       => $pidx['updateAt']
//                                ];
//
//                                $command = 'curl -X ';
//                                $command .= '-d "' . http_build_query($data) . '" ';
//
//                                $command .= $updateWebHook . ' -s > /dev/null 2>&1 &';
//                                LogMessage::debug('curl command :: ' . $command);
//                                passthru($command);
//                            } catch (\Exception $e) {
//                                LogMessage::error('Child Product Webhook Error :: ' . $cidx);
//                            }
//                        }
//
//                    } catch (\Exception $ex) {
//                        LogMessage::error('Product Webhook Error :: ' . $pidx['productKey']);
//                    }
//                }
//
//            } catch (\Exception $eex) {
//                LogMessage::error('Product Webhook Error');
//            }
//        }

            $endTime = CommonUtil::getMicroTime();

            LogMessage::info('TF Batch Start :: ' . $startTimeStemp);
            LogMessage::info('TF Batch End :: ' . CommonUtil::getDateTime() . ' ' . $endTime);
            LogMessage::info('TF Batch Runtime :: ' . ($endTime - $startTime));

            return $response->withJson($results, ErrorConst::SUCCESS_CODE);

        }

        /**
         * 특정 상품의 배치
         *
         * @param Request  $request
         * @param Response $response
         * @param          $args
         *
         * @return Response
         * @throws \GuzzleHttp\Exception\GuzzleException
         */
        public function setProductBatch(Request $request, Response $response, $args)
        {

            $productIdx = $args['product_idx'] ?? null;
            $areaIdx = $args['area_idx'] ?? null;

            try {

                if (false === CommonUtil::validateParams($args, ['product_idx', 'area_idx'], true)) {
                    throw new \Exception(null, ErrorConst::ERROR_NOT_FOUND_REQUIRE_FIELD);
                }

                // 모상품 상세정보
                $redisKey = TFConst::REDIS_TF_PRODUCT_REDIS_KEY . '_' . $productIdx;
                //if (false === ($productDetail = RedisUtil::getData($this->redis, $redisKey, $this->redisSession))) {
                LogMessage::info('TF product_idx :: ' . $productIdx);
                $targetUrl = TFConst::TF_URL . TFConst::TF_GET_PRODUCTS_URI . '/' . $productIdx;
                $productDetail = Http::httpRequest($targetUrl, $this->guzzleOpt);
                $productDetail['area_idx'] = $areaIdx;
                $this->_setRedis($redisKey, $productDetail);
                //}

                try {

                    $tourItemIdx = 0;

                    //자상품 상세정보
                    if (isset($productDetail['tour_items']) && is_array($productDetail['tour_items'])) {
                        foreach ($productDetail['tour_items'] as $ti) {

                            $tourItemIdx = $ti['tour_item_idx'];
                            $redisKey = TFConst::REDIS_TF_SUB_PRODUCT_REDIS_KEY . '_' . $tourItemIdx;
                            if (false === ($productSubDetail = RedisUtil::getData($this->redis, $redisKey, $this->redisSession))) {

                                LogMessage::info('TF tour_item_idx :: ' . $tourItemIdx);
                                $productSubDetail[] = [
                                    'area_idx'    => $areaIdx,
                                    'product_idx' => $productIdx,
                                    'tour_item'   => $ti
                                ];

                                $this->_setRedis($redisKey, $productSubDetail);
                            }
                        }
                    }

                    // 픽업 상품
                    if (isset($productDetail['transfer_items']) && is_array($productDetail['transfer_items'])) {
                        foreach ($productDetail['transfer_items'] as $ti) {

                            $tourItemIdx = $ti['transfer_item_idx'];
                            $redisKey = TFConst::REDIS_TF_SUB_PRODUCT_REDIS_KEY . '_' . $tourItemIdx;
                            if (false === ($productSubDetail = RedisUtil::getData($this->redis, $redisKey, $this->redisSession))) {

                                LogMessage::info('TF tour_item_idx :: ' . $tourItemIdx);
                                $productSubDetail[] = [
                                    'area_idx'      => $areaIdx,
                                    'product_idx'   => $productIdx,
                                    'transfer_item' => $ti
                                ];

                                $this->_setRedis($redisKey, $productSubDetail);
                            }
                        }
                    }

                } catch (\Exception $ee) {
                    LogMessage::error('SubProduct Detail Error :: ' . $productIdx . '-' . $tourItemIdx . '_' . $areaIdx);
                    LogMessage::error($ee->getMessage());
                    LogMessage::info(json_encode($ti, JSON_UNESCAPED_UNICODE));
                }

                $products[] = [
                    'productKey' => $productIdx,
                    'updatedAt'  => $productDetail['modify_datetime'] ?? null,
                    'title'      => $productDetail['product_ko']
                ];

                $results = [
                    'result' => ErrorConst::SUCCESS_STRING,
                ];

            } catch (\Exception $ex) {

                LogMessage::error('Product Detail Error :: ' . $productIdx . '_' . $areaIdx);
                LogMessage::error($ex->getMessage());
                LogMessage::info(json_encode($productDetail, JSON_UNESCAPED_UNICODE));

                $results = [
                    'result' => ErrorConst::FAIL_STRING,
                    'data'   => [
                        'message' => $this->_getErrorMessage($ex),
                    ]
                ];
            }


            return $response->withJson($results, ErrorConst::SUCCESS_CODE);

        }


        /**
         * 타이드 스퀘어 권한 체크
         *
         * @param Request  $request
         * @param Response $response
         *
         * @return Response
         */
        public function authCheck(Request $request, Response $response)
        {
            try {

                $params = $request->getAttribute('params');

                if (false === CommonUtil::validateParams($params, ['supplierCode'])) {
                    throw new \Exception(null, ErrorConst::ERROR_NOT_FOUND_REQUIRE_FIELD);
                }

                if ($params['supplierCode'] !== TFConst::TS_SUPPLIER_CODE) {
                    LogMessage::error('TS supplierCode :: ' . $params['supplierCode']);
                    throw new \Exception('', ErrorConst::ERROR_RDB_APP_AUTH_FAIL);
                }

                $accessToken = '';
                $headers = $request->getHeaders();

                // supplier code
                if (isset($headers['HTTP_AUTHORIZATION']) && ! empty($headers['HTTP_AUTHORIZATION'])) {
                    $accessToken = str_replace('Bearer ', '', $headers['HTTP_AUTHORIZATION'][0]);
                } elseif (isset($headers['HTTP_ACCESS_TOKEN']) && ! empty($headers['HTTP_ACCESS_TOKEN'])) {
                    $accessToken = $headers['HTTP_ACCESS_TOKEN'][0];
                }

                if ($accessToken !== TFConst::TS_ACCESS_TOKEN) {
                    LogMessage::error('TS Auth Fail - AccessToken :: ' . $accessToken);
                    throw new \Exception('', ErrorConst::ERROR_RDB_APP_AUTH_FAIL);
                }

                // supplier code
                if ( ! isset($headers['HTTP_SUPPLIER_CODE'])) {
                    LogMessage::error('TS Auth Fail - Supplier Code is not exist');
                    throw new \Exception('', ErrorConst::ERROR_RDB_APP_AUTH_FAIL);
                }

                if ($headers['HTTP_SUPPLIER_CODE'][0] !== TFConst::TS_SUPPLIER_CODE) {
                    LogMessage::error('TS Auth Fail - Supplier Code :: ' . $accessToken);
                    throw new \Exception('', ErrorConst::ERROR_RDB_APP_AUTH_FAIL);
                }

                $results = [
                    'result' => true
                ];

            } catch (\Exception $ex) {

                $results = [
                    'result' => false,
                    'data'   => [
                        'message' => $this->_getErrorMessage($ex),
                    ]
                ];
            }

            return $response->withJson($results, ErrorConst::SUCCESS_CODE);

        }


        /**
         * * 예약진행
         *
         * @param Request  $request
         * @param Response $response
         *
         * 1. Tour(인원별): tour_item_idx, tour_season_idx, tour_customer_idx
         * 2. Transfer(차량별): transfer_item_idx, transfer_price_idx
         * 3. 공통사항: 이용일, 여행자정보, 옵션 등
         *
         * @return Response
         * @throws \GuzzleHttp\Exception\GuzzleException
         */
        public function addBooking(Request $request, Response $response)
        {

            $adultAmount = 0;
            $childrenAmount = 0;
            $totalAmount = 0;

            try {

                $params = $request->getAttribute('params');

                if (false === CommonUtil::validateParams($params, ['product', 'adults', 'children', 'arrivalDate', 'selectTimeslot'], true)) {
                    throw new \Exception(null, ErrorConst::ERROR_NOT_FOUND_REQUIRE_FIELD);
                }

                $product = $params['product'];
                $arrivalDate = date('Ymd', strtotime($params['arrivalDate']));

                $result = [
                    'product_idx'         => (int)$product['product_idx'],
                    'sub_product_idx'     => (int)$params['productTypeKey'],
                    'customer_salutation' => $params['customer_salutation'],
                    'customer_firstName'  => $params['customer_firstName'],
                    'customer_lastName'   => $params['customer_lastName'],
                    'customer_email'      => $params['customer_email'],
                    'customer_phone'      => $params['customer_phone'],
                    'arrival_date'        => $params['arrivalDate'],
                    'select_time_slot'    => $params['selectTimeslot'] ?? '00:00',
                    'message'             => $params['message'] ?? '',
                    'options'             => $params['options'] ?? [],
                    'adults'              => (int)($params['adults'] ?? 0),
                    'children'            => (int)($params['children'] ?? 0),
                ];

                // 투어 상품과 픽업 상품이 나뉨
                switch (true) {
                    case (isset($product['tour_items']) && is_array($product['tour_items'])) : // 투어상품

                        if (false === ($tourIdx = array_search($params['productTypeKey'], array_column($product['tour_items'], 'tour_item_idx')))) {
                            throw new \Exception('', ErrorConst::ERROR_RDB_NO_DATA_EXIST);
                        }

                        $tourItem = $product['tour_items'][$tourIdx];
                        $tourItemIdx = (int)$tourItem['tour_item_idx'];
                        $tourSuppliers = $tourItem['tour_suppliers'] ?? null;
                        $result['tour_item_idx'] = $tourItemIdx;
                        $tourCustomIdxs = [];

                        if (empty($tourSuppliers)) {
                            throw new \Exception('', ErrorConst::ERROR_RDB_NO_DATA_EXIST);
                        }

                        foreach ($tourSuppliers as $ts) {
                            if (isset($ts['tour_seasons']) && is_array($ts['tour_seasons'])) {
                                foreach ($ts['tour_seasons'] as $tss) {

                                    // 날짜 비교
                                    if (false === $this->_validRangeDate($tss['season_date_from'], $tss['season_date_to'], $arrivalDate)) {
                                        continue;
                                    }

                                    $result['tour_season_idx'] = (int)$tss['tour_season_idx'];

                                    // 성인 가격
                                    if ( ! empty($params['adults'])) {
                                        $adultsCount = $params['adults'];
                                        do {
                                            $adultsCount--;
                                            foreach ($tss['tour_prices'] as $tp) {
                                                if ($tp['sale_unit_ko'] === '성인') {
                                                    $tourCustomIdxs[] = (int)$tp['tour_customer_idx'];
                                                    $adultAmount += $tp['sale_price'];
                                                    $totalAmount += $tp['sale_price'];
                                                }
                                            }
                                        } while (0 < $adultsCount);
                                    }

                                    // 아동가격
                                    if ( ! empty($params['children'])) {
                                        $childrenCount = $params['children'];
                                        do {
                                            $childrenCount--;
                                            foreach ($tss['tour_prices'] as $tp) {
                                                if ($tp['sale_unit_ko'] === '아동') {
                                                    $tourCustomIdxs[] = (int)$tp['tour_customer_idx'];
                                                    $childrenAmount += $tp['sale_price'];
                                                    $totalAmount += $tp['sale_price'];
                                                }
                                            }
                                        } while (0 < $childrenCount);
                                    }

                                }
                            }
                        }

                        $result['tour_customer_idx'] = $tourCustomIdxs;

                        break;

                    case (isset($product['transfer_items']) && is_array($product['transfer_items'])) :  // 픽업상품

                        if (false === ($tourIdx = array_search($params['productTypeKey'],
                                array_column($product['transfer_items'], 'transfer_item_idx')))
                        ) {
                            throw new \Exception('', ErrorConst::ERROR_RDB_NO_DATA_EXIST);
                        }

                        $tourItem = $product['transfer_items'][$tourIdx];
                        $tourItemIdx = $tourItem['transfer_item_idx'];
                        $result['transfer_item_idx'] = (int)$tourItemIdx;

                        $tourSuppliers = $tourItem['transfer_suppliers'] ?? null;

                        if (empty($tourSuppliers)) {
                            throw new \Exception('', ErrorConst::ERROR_RDB_NO_DATA_EXIST);
                        }

                        foreach ($tourSuppliers as $ts) {
                            if (isset($ts['transfer_seasons']) && is_array($ts['transfer_seasons'])) {
                                foreach ($ts['transfer_seasons'] as $tss) {

                                    // 날짜 비교
                                    if (false === $this->_validRangeDate($tss['season_date_from'], $tss['season_date_to'], $arrivalDate)) {
                                        continue;
                                    }

                                    if ( ! isset($params['options']['perBooking'][0]['id'])) {
                                        LogMessage::error('No Option data');
                                        throw new \Exception(null, ErrorConst::ERROR_NOT_FOUND_REQUIRE_FIELD);
                                    }

                                    $perBookingOption = $params['options']['perBooking'] ?? null;
                                    $transferPriceIdx = $perBookingOption[0]['id'];

                                    if (false === ($tourIdx = array_search($transferPriceIdx,
                                            array_column($tss['transfer_prices'], 'transfer_price_idx')))
                                    ) {
                                        continue;
                                    }

                                    $result['transfer_price_idx'] = (int)$transferPriceIdx;
                                    array_shift($perBookingOption);

                                    $result['options']['perBooking'] = $perBookingOption;

                                }
                            }
                        }

                        break;
                }

                $result['amount'] = [
                    'adult' => $adultAmount,
                    'child' => $childrenAmount,
                    'total' => $totalAmount
                ];

                $result['event_key'] = $this->_getRedisEventKey(['action' => 'addBooking', 'datetime'   => date('Y-m-d H:i:s')]);

                $this->guzzleOpt['json'] = $result;
                $this->guzzleOpt['allow_redirects'] = true;
                $targetUrl = TFConst::TF_BOOK_ADD_URL;
                $addResult = Http::httpRequest($targetUrl, $this->guzzleOpt, CommonConst::REQ_METHOD_POST);
                //$addResult['booking_idx'] = rand(10000, 99999);

                if (!empty($addResult['errors'])) {
                    throw new \Exception('예약 생성에 실패하였습니다.');
                }

                if (!isset($addResult['book']['book_cd']) || empty($addResult['book']['book_cd'])) {
                    throw new \Exception('예약 생성에 실패하였습니다.');
                }

                $bookIdx = $addResult['book']['book_cd'];
                switch ($addResult['book']['book_status_cd']) {
                    case TFConst::TF_BOOK_CD_INS :
                    case TFConst::TF_BOOK_CD_ORD :
                    case TFConst::TF_BOOK_CD_REQ :
                        $status = TFConst::RESERVE_STATUS_RESV;
                        break;
                    default :
                        throw new \Exception('예약 생성에 실패하였습니다.');
                }

                $results = [
                    'result' => [
                        'success' => true,
                        'status' => $status,
                        'bookingKey' => $bookIdx,
                        'totalAmount' => $totalAmount,
                    ],
                ];

                $redisDb = CommonConst::REDIS_MESSAGE_SESSION;
                $redisKey = TFConst::REDIS_TF_BOOKING_REDIS_KEY . '_' . $bookIdx;

                RedisUtil::setDataWithExpire($this->redis, $redisDb, $redisKey, CommonConst::REDIS_SESSION_EXPIRE_TIME_DAY_7, [
                    'status'           => $status,
                    'bookingKey'       => $bookIdx,
                    'partnerReference' => $params['partnerReference'] ?? null,
                    'totalAmount'      => $totalAmount,
                    'refundAmount'     => $totalAmount,
                    'tf_result'        => $addResult
                ]);

            } catch (\Exception $ex) {

                $results = [
                    'result' => [
                        'success'     => false,
                        'status'      => TFConst::STATUS_ERROR,
                        'bookingKey'  => null,
                        'totalAmount' => $totalAmount,
                        'message'     => $this->_getErrorMessage($ex),
                    ]
                ];
            }

            return $response->withJson($results, ErrorConst::SUCCESS_CODE);

        }

        /**
         * 예약 진행
         *
         * @param Request  $request
         * @param Response $response
         * @param          $args
         *
         * @return Response
         * @throws \GuzzleHttp\Exception\GuzzleException
         */
        public function proceedBooking(Request $request, Response $response, $args)
        {
            try {

                if (false === CommonUtil::validateParams($args, ['bookingKey'], true)) {
                    throw new \Exception(null, ErrorConst::ERROR_NOT_FOUND_REQUIRE_FIELD);
                }

                $redisDb = CommonConst::REDIS_MESSAGE_SESSION;
                $redisKey = TFConst::REDIS_TF_BOOKING_REDIS_KEY . '_' . $args['bookingKey'];

                if (false === ($bookingStatusRedis = RedisUtil::getData($this->redis, $redisKey, $redisDb))) {
                    throw new \Exception(ErrorConst::ERROR_DESCRIPTION_ARRAY[ErrorConst::ERROR_RDB_NO_DATA_EXIST]);
                }

                if ($bookingStatusRedis['status'] !== TFConst::RESERVE_STATUS_RESV) {
                    throw new \Exception('예약 진행 가능한 주문이 아닙니다.');
                }

                $this->guzzleOpt['json'] = ['event_key' => $this->_getRedisEventKey([
                    'action' => 'proceedBooking',
                    'bookingKey' => $args['bookingKey'],
                    'datetime'   => date('Y-m-d H:i:s'),
                ])];

                $this->guzzleOpt['allow_redirects'] = true;
                $targetUrl = TFConst::TF_BOOK_PROC_URL . '/' . $args['bookingKey'];
                $procResult = Http::httpRequest($targetUrl, $this->guzzleOpt, CommonConst::REQ_METHOD_PATCH);

                LogMessage::info('booking proc result ::' . json_encode($procResult, JSON_UNESCAPED_UNICODE));

                if (!empty($procResult['errors'])) {
                    throw new \Exception('예약 진행에 실패하였습니다.');
                }

                if (!isset($procResult['book']['book_cd']) || empty($procResult['book']['book_cd'])) {
                    throw new \Exception('예약 진행에 실패하였습니다.');
                }

                $results = [
                    'success'          => true,
                    'status'           => TFConst::RESERVE_STATUS_WAIT,
                    'bookingKey'       => $procResult['book']['book_cd'],
                    'partnerReference' => $bookingStatusRedis['partnerReference'] ?? null,
                    'totalAmount'      => (int)$procResult['book']['sale_price'],
                    'refundAmount'     => (int)$procResult['book']['sale_price'],
                    'tf_result'        => $procResult
                ];

                RedisUtil::setDataWithExpire($this->redis, $redisDb, $redisKey, CommonConst::REDIS_SESSION_EXPIRE_TIME_DAY_7, $results);
                unset($results['refundAmount']);
                unset($results['tf_result']);

            } catch (\Exception $ex) {

                $results = [
                    'success'          => false,
                    'bookingKey'       => $args['bookingKey'],
                    'partnerReference' => null,
                    'totalAmount'      => 0,
                    'message'          => $ex->getMessage(),
                ];

            }

            return $response->withJson($results, ErrorConst::SUCCESS_CODE);

        }


        /**
         * 예약 취소
         *
         * @param Request  $request
         * @param Response $response
         * @param          $args
         *
         * @return Response
         */
        public function cancelBooking(Request $request, Response $response, $args)
        {
            try {

                if (false === CommonUtil::validateParams($args, ['bookingKey'], true)) {
                    throw new \Exception(null, ErrorConst::ERROR_NOT_FOUND_REQUIRE_FIELD);
                }

                $redisDb = CommonConst::REDIS_MESSAGE_SESSION;
                $redisKey = TFConst::REDIS_TF_BOOKING_REDIS_KEY . '_' . $args['bookingKey'];

                if (false === ($bookingStatusRedis = RedisUtil::getData($this->redis, $redisKey, $redisDb))) {
                    throw new \Exception('', ErrorConst::ERROR_RDB_NO_DATA_EXIST);
                }

                if ($bookingStatusRedis['status'] === TFConst::RESERVE_STATUS_CNCL || $bookingStatusRedis['status'] === TFConst::CANCEL_STATUS_DENY) {
                    throw new \Exception('예약 취소 가능한 주문이 아닙니다.');
                }

                if ($bookingStatusRedis['status'] === TFConst::CANCEL_STATUS_WAIT) {
                    throw new \Exception('예약 취소 진행중 입니다.');
                }

                $subject = '주문 취소 요청 이메일';
                $body = '<h1>취소요청 이메일</h1>'
                      . '<p>주문 키 : ' . $bookingStatusRedis['bookingKey'] . '</p>'
                      . '<p>주문금액 : ' . number_format($bookingStatusRedis['totalAmount']) . '</p>'
                      . '<p>환불금액 : ' . number_format($bookingStatusRedis['refundAmount']) . '</p>'
                      ;

                if (false === AwsUtil::sendEmail($this->recipients, $subject, $body)) {
                    throw new \Exception(null, ErrorConst::ERROR_SEND_EMAIL);
                }

                $results = [
                    'success'      => true,
                    'status'       => TFConst::CANCEL_STATUS_WAIT,
                    'bookingKey'   => $args['bookingKey'],
                    'totalAmount'  => $bookingStatusRedis['totalAmount'],
                    'refundAmount' => $bookingStatusRedis['refundAmount'],
                    'tf_result'    => $bookingStatusRedis['tf_result']
                ];

                RedisUtil::setDataWithExpire($this->redis, $redisDb, $redisKey, CommonConst::REDIS_SESSION_EXPIRE_TIME_DAY_7, $results);
                unset($results['tf_result']);
                unset($results['totalAmount']);

            } catch (\Exception $ex) {

                $results = [
                    'success'      => false,
                    'status'       => TFConst::STATUS_ERROR,
                    'bookingKey'   => $args['bookingKey'],
                    'refundAmount' => 0,
                    'message'      => $this->_getErrorMessage($ex),
                ];

            }

            return $response->withJson($results, ErrorConst::SUCCESS_CODE);
        }


        /**
         * 예약 상태 확인
         *
         * @param Request  $request
         * @param Response $response
         * @param          $args
         *
         * @return Response
         * @throws \GuzzleHttp\Exception\GuzzleException
         */
        public function checkBookingStatus(Request $request, Response $response, $args)
        {
            try {

                if (false === CommonUtil::validateParams($args, ['bookingKey'], true)) {
                    throw new \Exception(null, ErrorConst::ERROR_NOT_FOUND_REQUIRE_FIELD);
                }

                $redisDb = CommonConst::REDIS_MESSAGE_SESSION;
                $redisKey = TFConst::REDIS_TF_BOOKING_REDIS_KEY . '_' . $args['bookingKey'];

                if (false === ($bookingStatus = RedisUtil::getData($this->redis, $redisKey, $redisDb))) {

                    $this->guzzleOpt['json'] = ['event_key' => $this->_getRedisEventKey([
                        'action' => 'checkBookingStatus',
                        'bookingKey' => $args['bookingKey'],
                        'datetime'   => date('Y-m-d H:i:s'),
                    ])];

                    $this->guzzleOpt['allow_redirects'] = true;
                    $targetUrl = TFConst::TF_BOOK_PROC_URL . '/' . $args['bookingKey'];
                    $bookingStatus = Http::httpRequest($targetUrl, $this->guzzleOpt, CommonConst::REQ_METHOD_GET);
                }

                if (!empty($bookingStatus['errors'])) {
                    throw new \Exception('예약 조회에 실패하였습니다.');
                }

                $redisStatus = $bookingStatus['status'] ?? null;
                $bookingStatusData = (isset($bookingStatus['tf_result'])) ? $bookingStatus['tf_result'] : $bookingStatus;
                $bookingStatus = (isset($bookingStatus['tf_result'])) ? $bookingStatus['tf_result']['book'] : $bookingStatus['book'];

                $results = [
                    'status'           => $redisStatus ?? $this->_getStatusCode($bookingStatus['book_status_cd']),
                    'bookingKey'       => $bookingStatus['book_cd'],
                    'partnerReference' => $bookingStatus['partnerReference'] ?? null,
                    'totalAmount'      => $bookingStatus['sale_price'] ?? 0,
                    'refundAmount'     => $bookingStatus['sale_price'] ?? 0,
                    'tf_result'        => $bookingStatusData
                ];

                RedisUtil::setDataWithExpire($this->redis, $redisDb, $redisKey, CommonConst::REDIS_SESSION_EXPIRE_TIME_DAY_7, $results);
                unset($results['tf_result']);

            } catch (\Exception $ex) {

                $results = [
                    'status'       => TFConst::STATUS_ERROR,
                    'bookingKey'   => $args['bookingKey'],
                    'message'      => $this->_getErrorMessage($ex),
                ];

            }

            return $response->withJson($results, ErrorConst::SUCCESS_CODE);
        }


        /**
         * 트레블포레스트에서 주문관련 업데이트시 호출할 Webhook
         *
         * @param Request  $request
         * @param Response $response
         * @param          $args
         *
         * @return Response
         * @throws \GuzzleHttp\Exception\GuzzleException
         */
        public function bookingUpdate(Request $request, Response $response, $args)
        {
            $results = $this->jsonResult;
            $tryCount = 0;
            $maxTryCount = $this->maxTryCount;
            $updateType = null;

            try {

                if (false === CommonUtil::validateParams($args, ['bookingKey', 'updateType'], true)) {
                    throw new \Exception(null, ErrorConst::ERROR_NOT_FOUND_REQUIRE_FIELD);
                }

                $updateType = $this->_getStatusCode($args['updateType']);

                if (empty($updateType)) {
                    throw new \Exception(null, ErrorConst::ERROR_STEP_FAIL);
                }

                $this->guzzleOpt['json'] = ['event_key' => $this->_getRedisEventKey([
                    'action'     => 'bookingUpdate',
                    'updateType' => $args['updateType'],
                    'bookingKey' => $args['bookingKey'],
                    'datetime'   => date('Y-m-d H:i:s'),
                ])];

                $this->guzzleOpt['allow_redirects'] = true;
                $statusTargetUrl = TFConst::TF_BOOK_PROC_URL . '/' . $args['bookingKey'];
                $bookingStatusRedis = Http::httpRequest($statusTargetUrl, $this->guzzleOpt, CommonConst::REQ_METHOD_GET);
                $curStatus = $bookingStatusRedis['book']['book_status_cd'];

                switch ($args['updateType']) {
                    case TFConst::TF_BOOK_CD_CFM :
                        if ($curStatus != TFConst::TF_BOOK_CD_BOK) {
                            throw new \Exception(null, ErrorConst::ERROR_STEP_FAIL);
                        }
                        break;
                    case TFConst::TF_BOOK_CD_CCL :
                        if ($curStatus == TFConst::TF_BOOK_CD_CCL || $curStatus == TFConst::TF_BOOK_CD_CLS) {
                            throw new \Exception(null, ErrorConst::ERROR_STEP_FAIL);
                        }
                        break;
                }

                if (APP_ENV == APP_ENV_PRODUCTION || APP_ENV === APP_ENV_STAGING) {

                    $targetUrl = TFConst::TS_RESERVE_WEBHOOK_URL;
                    $updateParams = [
                        'bookingKey' => $args['bookingKey'],
                        'updateType' => $updateType,
                        'updateAt'   => date('Y-m-d H:i:s')
                    ];

                    do {

                        LogMessage::info('try Count :: ' . $tryCount . ' - noWait :: ' . $this->noWait);
                        $result = $this->_webhook($targetUrl, $updateParams, $this->noWait, 300);
                        $tryCount++;

                    } while ($result['success'] === false && $tryCount <= $maxTryCount);

                    if ( ! isset($result['success']) || true !== $result['success']) {
                        throw new \Exception('Webhook Error');
                    }

                }

                $redisDb = CommonConst::REDIS_MESSAGE_SESSION;
                $redisKey = TFConst::REDIS_TF_BOOKING_REDIS_KEY . '_' . $args['bookingKey'];

                $results = [
                    'status'           => $updateType,
                    'bookingKey'       => $bookingStatusRedis['book']['book_cd'],
                    'partnerReference' => null,
                    'totalAmount'      => $bookingStatusRedis['book']['sale_price'],
                    'refundAmount'     => $bookingStatusRedis['book']['sale_price'],
                    'tf_result'        => $bookingStatusRedis,
                ];

                RedisUtil::setDataWithExpire($this->redis, $redisDb, $redisKey, CommonConst::REDIS_SESSION_EXPIRE_TIME_DAY_7, $results);
                unset($results['tf_result']);

                $body = '<h1>주문정보 업데이트 처리요청</h1>' . '<p>주문 키 : ' . $args['bookingKey'] . '</p>' . '<p>' . $args['updateType'] . ' 처리 성공</p>';

            } catch (\Exception $ex) {

                $body = '<h1>주문정보 업데이트 처리요청</h1>' . '<p>주문 키 : ' . $args['bookingKey'] . '</p>' . '<p>' . $args['updateType'] . ' 처리 실패</p>';

                $results = [
                    'status'       => TFConst::STATUS_ERROR,
                    'bookingKey'   => $args['bookingKey'],
                    'message'      => $this->_getErrorMessage($ex),
                ];
            }

            try {
                if (false === AwsUtil::sendEmail($this->recipients, '주문정보 업데이트 결과', $body)) {
                    throw new \Exception(null, ErrorConst::ERROR_SEND_EMAIL);
                }
            } catch (\Exception $ex) {

            }

            return $response->withJson($results, ErrorConst::SUCCESS_CODE);
        }


        /**
         * 트레블포레스트에서 상품 관련 업데이트시 호출할 Webhook
         *
         * @param Request  $request
         * @param Response $response
         * @param          $args
         *
         * @return Response
         * @throws \GuzzleHttp\Exception\GuzzleException
         */
        public function productUpdate(Request $request, Response $response, $args)
        {
            try {

                $results = $this->jsonResult;
                $tryCount = 0;
                $maxTryCount = $this->maxTryCount;
                $updateType = null;
                $targetUrl = TFConst::TS_PRODUCT_WEBHOOK_URL;

                if (false === CommonUtil::validateParams($args, ['productKey', 'updateType'], true)) {
                    throw new \Exception(null, ErrorConst::ERROR_NOT_FOUND_REQUIRE_FIELD);
                }

                switch ($args['updateType']) {
                    case 1 : // 상품추가
                        $updateType = 'ADD';
                        break;
                    case 2 : // 상품삭제
                        $updateType = 'DELETE';
                        break;
                    case 3 : // 업데이트
                        $updateType = 'UPDATE';
                        break;
                }

                if (empty($updateType)) {
                    throw new \Exception(null, ErrorConst::ERROR_NOT_FOUND_REQUIRE_FIELD);
                }

                $updateParams = [
                    'productKey'     => $args['productKey'],
                    'productTypeKey' => $args['productTypeKey'] ?? null,
                    'updateType'     => $updateType,
                    'updateAt'       => date('Y-m-d H:i:s')
                ];

                do {

                    LogMessage::info('try Count :: ' . $tryCount . ' - noWait :: ' . $this->noWait);
                    $result = $this->_webhook($targetUrl, $updateParams, $this->noWait);
                    $tryCount++;

                } while ($result['success'] === false && $tryCount <= $maxTryCount);

                if ( ! isset($result['success']) || true !== $result['success']) {
                    throw new \Exception('Webhook Error');
                }

            } catch (\Exception $ex) {

                $results = [
                    'result' => false,
                    'data'   => [
                        'message' => $this->_getErrorMessage($ex),
                    ]
                ];

            }

            return $response->withJson($results, ErrorConst::SUCCESS_CODE);
        }


        /**
         * @param      $targetUrl
         * @param      $params
         * @param bool $noWait
         * @param int  $sleep
         *
         * @return array
         * @throws \GuzzleHttp\Exception\GuzzleException
         */
        private function _webhook($targetUrl, $params, $noWait = true, $sleep = 1)
        {
            if ($noWait === false) {
                sleep($sleep);
            }

            try {

                $this->guzzleOpt['json'] = $params;
                $this->guzzleOpt['allow_redirects'] = true;
                $this->guzzleOpt['headers'] = [
                    'Access-Token'  => TFConst::TS_ACCESS_TOKEN,
                    'Supplier-Code' => TFConst::TS_SUPPLIER_CODE,
                    'Content-Type'  => 'application/json',
                    'Accept'        => 'application/json'
                ];

                $result = Http::httpRequest($targetUrl, $this->guzzleOpt, CommonConst::REQ_METHOD_PUT);

                if ( ! isset($result['success']) || true !== $result['success']) {
                    $this->noWait = false;
                    $result['success'] = false;
                    LogMessage::error('Webhook Error :: ' . json_encode($result, JSON_UNESCAPED_UNICODE));
                }

            } catch (\Exception $ex) {
                LogMessage::error('Webhook Error :: ' . $ex->getMessage());
                $result['success'] = false;
            }

            return $result;

        }


        /**
         * 신청 가능 날짜
         *
         * @param $from
         * @param $to
         * @param $arrivalDate
         *
         * @return bool
         */
        private function _validRangeDate($from, $to, $arrivalDate)
        {
            $tourDateFrom = date('Y-m-d', strtotime($from));
            $tourDateTo = date('Y-m-d', strtotime($to));

            if ($arrivalDate < $tourDateFrom || $arrivalDate > $tourDateTo) {
                LogMessage::error('No Option data');

                return false;
            }

            return true;
        }


        /**
         * 트레블 포레스트 지역 정보 패치
         *
         * @return array|bool|mixed|string
         * @throws \GuzzleHttp\Exception\GuzzleException
         */
        private function _getArea()
        {
            $redisKey = TFConst::REDIS_TF_AREA_REDIS_KEY;

            if (false === ($area = RedisUtil::getData($this->redis, $redisKey, $this->redisSession))) {
                $targetUrl = TFConst::TF_URL . TFConst::TF_GET_AREA_URI;
                $area = Http::httpRequest($targetUrl, $this->guzzleOpt);
                $this->_setRedis($redisKey, $area);
            }

            return $area;
        }


        /**
         *
         * @param           $redisKey
         * @param           $data
         * @param float|int $expire
         */
        private function _setRedis($redisKey, $data, $expire = CommonConst::REDIS_SESSION_EXPIRE_TIME_DAY_2)
        {
            $redisSession = TFConst::REDIS_TF_SESSION;

            try {
                //RedisUtil::setDataWithExpire($this->redis, $redisSession, $redisKey, $expire, $data);
                RedisUtil::setData($this->redis, $redisSession, $redisKey, $data);
            } catch (\Exception $ex) {
                LogMessage::error('TravelForest - Set Redis Error (' . $redisKey . ')');
            }
        }

        /**
         * @param $tf_status
         *
         * @return string
         */
        private function _getStatusCode($tf_status)
        {
            switch ($tf_status) {
                case TFConst::TF_BOOK_CD_ORD :
                case TFConst::TF_BOOK_CD_REQ :
                case TFConst::TF_BOOK_CD_INS :
                    $status = TFConst::RESERVE_STATUS_RESV;
                    break;
                case TFConst::TF_BOOK_CD_BOK :
                    $status = TFConst::RESERVE_STATUS_WAIT;
                    break;
                case TFConst::TF_BOOK_CD_CFM :
                    $status = TFConst::RESERVE_STATUS_CFRM;
                    break;
                case TFConst::TF_BOOK_CD_CLS :
                case TFConst::TF_BOOK_CD_CCL :
                    $status = TFConst::RESERVE_STATUS_CNCL;
                    break;
                default :
                    $status = null;
            }

            return $status;

        }


    }