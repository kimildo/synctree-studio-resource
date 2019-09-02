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
            'connect_timeout' => CommonConst::HTTP_CONNECT_TIMEOUT,
            'headers'         => [
                'User-Agent' => 'Synctree/2.1 - ' . APP_ENV
            ]
        ];

        private $noWait = true;
        private $maxTryCount = 10;
        private $redisSession = TFConst::REDIS_TF_SESSION;
        private $recipients = [];
        private $exceptProductKeys = [7055];

        public function __construct(ContainerInterface $ci)
        {
            parent::__construct($ci);

            $this->recipients[] = 'info@travelforest.co.kr';
            if (APP_ENV === APP_ENV_STAGING) {
                $this->recipients[] = 'kimildo78@nntuple.com';
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

                $params = $request->getAttribute('params');

                if (false === ($products = RedisUtil::getData($this->redis, TFConst::REDIS_TF_PRODUCTS_REDIS_KEY, $this->redisSession))) {
                    throw new \Exception('', ErrorConst::ERROR_RDB_NO_DATA_EXIST);
                }

                $results = $products;

                if (!empty($params['productKeys'])) {

                    $results = [];
                    $productKeys = array_column($products, 'productKey');

                    foreach ($params['productKeys'] as $pkey) {
                        if (false !== ($k = array_search($pkey, $productKeys))) {
                            $results[] = [
                                'productKey' => $pkey,
                                'title' => $products[$k]['title'] ?? '',
                            ];
                        }
                    }
                }

            } catch (\Exception $ex) {
                $results = [
                    'code' => ErrorConst::FAIL_CODE,
                    'message' => $this->_getErrorMessage($ex, true, ['subject' => '', 'body' => '모상품 목록 조회 에러']),
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
            $params = $request->getAttribute('params');

            try {

                if (false === CommonUtil::validateParams($params, ['product'], true)) {
                    throw new \Exception(null, ErrorConst::ERROR_RDB_NO_DATA_EXIST);
                }

                $product = $params['product'];
                $dateSearchFlag = false;

                if ( ! is_array($product) || empty($product) ) {
                    throw new \Exception(null, ErrorConst::ERROR_RDB_NO_DATA_EXIST);
                }

                // 제외할 상품목록
                if (in_array($product['product_idx'], $this->exceptProductKeys)) {
                    throw new \Exception(null, ErrorConst::ERROR_RDB_NO_DATA_EXIST);
                }

                $tourOptions = [];
                $priceArr = [];
                $priceAdultArr = [];
                $priceTeenArr = [];
                $priceChildArr = [];
                $priceInfantArr = [];
                $paxArr = [];
                $saleAvailableTour = [];
                $pDate = [];

                $maxTeenAge = $minTeenAge = 0;
                $maxChildAge = $minChildAge = 0;
                $maxInfantPriceAge = $minInfantPriceAge = 0;

                // 날짜 검색이면 날짜별 배열을 만든다.
                if (isset($params['fromDate']) && isset($params['toDate']) && ! empty($params['fromDate']) && ! empty($params['toDate'])) {

                    $dateSearchFlag = true;

                    $fromDate = date('Y-m-d', strtotime($params['fromDate']));
                    $toDate = date('Y-m-d', strtotime($params['toDate']));

                    $fromDateTime = new \DateTime($fromDate);
                    $toDateTime = new \DateTime($toDate);

                    $dateDiff = date_diff($fromDateTime, $toDateTime);
                    $dateDiff = $dateDiff->days + 1;

                    if ($dateDiff > 732) {
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

                        $voucherUse = '';
                        $inExClude = null;
                        $refundPolicy = null;
                        $isNoneRefund = false; // 환불불가
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
                            $productDetail['title'] = $this->_replaceSpecialChar($productDetail['title']);

                            $productDetail['durationDays'] = (int)($tourItem['duration_day'] ?? 0);
                            $productDetail['durationHours'] = (int)($tourItem['duration_hour'] ?? 0);
                            $productDetail['durationMins'] = (int)($tourItem['duration_minute'] ?? 0);
                            $productDetail['minAdultAge'] = 0;
                            $productDetail['maxAdultAge'] = 99;
                            $productDetail['hasChildPrice'] = false;
                            $productDetail['minChildAge'] = 0;

                            if (isset($tourItem['tour_suppliers'])) {
                                foreach ($tourItem['tour_suppliers'] as $tsp) {

                                    // 바우처 정보
                                    $voucherUse .= (!empty($tsp['voucher_content'])) ? $tsp['voucher_content'] : $tsp['note_content'];

                                    // 환불규정
                                    $refundPolicy = $tsp['cancel_content'] ?? '';

                                    // 환불 불가여부
                                    $isNoneRefund = (isset($tsp['cancel_type_cd']) && $tsp['cancel_type_cd'] == 'refund') ? false : true;

                                    // 불/포함사항
                                    $inExClude .= (!empty($tsp['include_content'])) ? '포함사항' . "\r\n" . $this->_replaceSpecialChar($tsp['include_content']) . "\r\n\r\n" : '';
                                    $inExClude .= (!empty($tsp['exclude_content'])) ? '불포함사항' . "\r\n" . $this->_replaceSpecialChar($tsp['exclude_content']) : '';

                                    // 날짜 검색이면
                                    if (true === $dateSearchFlag) {

                                        if ( ! empty($pDate)) {
                                            foreach ($pDate as $sDate) {

                                                $tmpDate = date('Y-m-d', strtotime($sDate));
                                                $tmpWeek = date('w', strtotime($sDate));
                                                $availAbleDate = false;
                                                $priceAdultArr = $priceTeenArr = $priceChildArr = $priceInfantArr = $priceBabyArr = [0];

                                                foreach ($tsp['tour_seasons'] as $ts) {

                                                    $weeks = explode(',', $ts['season_week']);
                                                    $tourDateFrom = date('Y-m-d', strtotime($ts['season_date_from']));
                                                    $tourDateTo = date('Y-m-d', strtotime($ts['season_date_to']));
                                                    $tourSaleDateTo = date('Y-m-d',
                                                        strtotime($ts['season_date_to'] . ' -' . $ts['sale_day_to'] . ' days'));

                                                    if ($tmpDate >= $tourDateFrom && $tmpDate <= $tourSaleDateTo && (in_array($tmpWeek, $weeks))) {

                                                        $availAbleDate = true;
                                                        $priceLabels = array_column($ts['tour_prices'], 'sale_unit_ko');
                                                        $heightPattern = '/^[0-9]{2,3}((\s*cm)?\s*\~\s*[0-9]{2,3})?(\s*cm)?/i';

                                                        // 성인
                                                        if (false !== ($tpIdx = array_search(TFConst::TF_ADULT_LABEL, $priceLabels))) {
                                                            $priceArr[] = $ts['tour_prices'][$tpIdx]['sale_price'] ?? 0;
                                                            $priceAdultArr[] = $ts['tour_prices'][$tpIdx]['sale_price'] ?? 0;
                                                            $productDetail['minAdultAge'] = (int)$ts['tour_prices'][$tpIdx]['customer_age_from'];
                                                            if (true == $match = preg_match($heightPattern, $ts['tour_prices'][$tpIdx]['customer_content'], $matches)) {
                                                                $adultSubLabel = ' (' . $ts['tour_prices'][$tpIdx]['customer_content'] . ')';
                                                            }
                                                        }

                                                        // 청소년 (teen) -> 경로요금으로
                                                        if (false !== ($tpIdx = array_search(TFConst::TF_TEENAGE_LABEL, $priceLabels))) {
                                                            $productDetail['hasSeniorPrice'] = true;
                                                            $priceArr[] = $ts['tour_prices'][$tpIdx]['sale_price'];
                                                            $priceTeenArr[] = $ts['tour_prices'][$tpIdx]['sale_price'];
                                                            $maxTeenAge = (int)$ts['tour_prices'][$tpIdx]['customer_age_to'];
                                                            $minTeenAge = (int)$ts['tour_prices'][$tpIdx]['customer_age_from'];
                                                            if (true == $match = preg_match($heightPattern, $ts['tour_prices'][$tpIdx]['customer_content'])) {
                                                                $seniorSubLabel = ' (' . $ts['tour_prices'][$tpIdx]['customer_content'] . ')';
                                                            }
                                                        }

                                                        // 아동 -> teenager
                                                        if (false !== ($tpIdx = array_search(TFConst::TF_CHILD_LABEL, $priceLabels))) {
                                                            $priceArr[] = $ts['tour_prices'][$tpIdx]['sale_price'];
                                                            $priceChildArr[] = $ts['tour_prices'][$tpIdx]['sale_price'];
                                                            $productDetail['hasTeenagerPrice'] = true;
                                                            $maxChildAge = (int)$ts['tour_prices'][$tpIdx]['customer_age_to'];
                                                            $minChildAge = (int)$ts['tour_prices'][$tpIdx]['customer_age_from'];
                                                            $productDetail['minTeenagerAge'] = (int)$ts['tour_prices'][$tpIdx]['customer_age_from'];
                                                            if (true == $match = preg_match($heightPattern, $ts['tour_prices'][$tpIdx]['customer_content'])) {
                                                                $teenSubLabel = ' (' . $ts['tour_prices'][$tpIdx]['customer_content'] . ')';
                                                            }
                                                        }

                                                        // 유아 -> child
                                                        if (false !== ($tpIdx = array_search(TFConst::TF_INFANT_LABEL, $priceLabels))) {
                                                            $priceArr[] = $ts['tour_prices'][$tpIdx]['sale_price'] ?? 0;
                                                            $priceInfantArr[] = $ts['tour_prices'][$tpIdx]['sale_price'] ?? 0;
                                                            $hasChild = true;
                                                            $productDetail['hasChildPrice'] = true;
                                                            $maxInfantPriceAge = (int)$ts['tour_prices'][$tpIdx]['customer_age_to'];
                                                            $minInfantPriceAge = (int)$ts['tour_prices'][$tpIdx]['customer_age_from'];
                                                            if (true == $match = preg_match($heightPattern, $ts['tour_prices'][$tpIdx]['customer_content'])) {
                                                                $childSubLabel = ' (' . $ts['tour_prices'][$tpIdx]['customer_content'] . ')';
                                                            }
                                                        }

                                                        // 영유아 --> infant
                                                        if (false !== ($tpIdx = array_search(TFConst::TS_INFANT_LABEL, $priceLabels))) {
                                                            $hasBaby = true;
                                                            $priceArr[] = $ts['tour_prices'][$tpIdx]['sale_price'] ?? 0;
                                                            $productDetail['allowInfants'] = true;
                                                            $productDetail['maxInfantAge'] = (int)$ts['tour_prices'][$tpIdx]['customer_age_to'];
                                                            if (!empty($ts['tour_prices'][$tpIdx]['sale_price'])) {
                                                                $priceBabyArr[] = $ts['tour_prices'][$tpIdx]['sale_price'] ?? 0;
                                                            }
                                                            if (true == $match = preg_match($heightPattern, $ts['tour_prices'][$tpIdx]['customer_content'])) {
                                                                $infantSubLabel = ' (' . $ts['tour_prices'][$tpIdx]['customer_content'] . ')';
                                                            }
                                                        }

                                                        $saleAvailableTour[] = [
                                                            'tour_season_idx' => $ts['tour_season_idx'],
                                                            'from_to'         => $tmpDate,
                                                            'available'       => true,
                                                            'adult_price'     => (int)(max(($priceAdultArr ?? 0))),
                                                            'senior_price'    => (int)(max(($priceTeenArr ?? 0))),
                                                            'teen_price'      => (int)(max(($priceChildArr ?? 0))),
                                                            'child_price'     => (int)(max(($priceInfantArr ?? 0))),
                                                            'infant_price'    => (int)(max(($priceBabyArr ?? 0))),
                                                        ];
                                                    }
                                                }

                                                if ($availAbleDate === false) {
                                                    $saleAvailableTour[] = [
                                                        'tour_season_idx' => 0,
                                                        'from_to'         => $tmpDate,
                                                        'available'       => false,
                                                        'adult_price'     => 0,
                                                        'senior_price'    => 0,
                                                        'teen_price'      => 0,
                                                        'child_price'     => 0,
                                                        'infant_price'    => 0,
                                                    ];
                                                }

                                            }
                                        }

                                    } else {

                                        // 최대/최소 가격 , 최소/최대 여행자수
                                        foreach ($tsp['tour_seasons'] as $ts) {

                                            $weeks = explode( ',', $ts['season_week']);
                                            $tourDateFrom = date('Y-m-d', strtotime($ts['season_date_from']));
                                            $tourDateTo = date('Y-m-d', strtotime($ts['season_date_to']));
                                            $tourSaleDateTo = date('Y-m-d', strtotime($ts['season_date_to'] . ' -' . $ts['sale_day_to'] . ' days'));
                                            $tmpWeek = date('w');

                                            // 가격에도 날짜가 있다
                                            if (false === $this->_validRangeDate($tourDateFrom, $tourDateTo)) {
                                                continue;
                                            }

                                            if (false === (in_array($tmpWeek, $weeks))) {
                                                //continue;
                                            }

                                            // 가격/여행객 수
                                            foreach ($ts['tour_prices'] as $tps) {

                                                switch ($tps['sale_unit_ko']) {
                                                    case TFConst::TF_ADULT_LABEL :
                                                        $productDetail['minAdultAge'] = (int)$tps['customer_age_from'];
                                                        $productDetail['maxAdultAge'] = (int)(($tps['customer_age_to'] > 0) ? $tps['customer_age_to'] : 99) ;
                                                        $priceArr[] = $tps['sale_price'] ?? 0;
                                                        $priceAdultArr[] = $tps['sale_price'] ?? 0;
                                                        break;

                                                    case TFConst::TF_TEENAGE_LABEL :
                                                        $priceTeenArr[] = $tps['sale_price'] ?? 0;
                                                        $productDetail['hasSeniorPrice'] = true;
                                                        break;

                                                    case TFConst::TF_CHILD_LABEL :
                                                        $priceChildArr[] = $tps['sale_price'];
                                                        $productDetail['hasTeenagerPrice'] = true;
                                                        $productDetail['minTeenagerAge'] = (int)$tps['customer_age_from'];
                                                        break;

                                                    case TFConst::TF_INFANT_LABEL :
                                                        $priceInfantArr[] = $tps['sale_price'];
                                                        $productDetail['hasChildPrice'] = true;
                                                        $productDetail['minChildAge'] = (int)$tps['customer_age_from'];
                                                        break;

                                                    case TFConst::TS_INFANT_LABEL :
                                                        $productDetail['maxInfantAge'] = (int)$tps['customer_age_to'];
                                                        $productDetail['allowInfants'] = true;
                                                        $priceBabyArr[] = ($tps['sale_price'] > 0) ? $tps['sale_price'] : 0;
                                                        break;
                                                }



                                                if (isset($tps['customer_count_from']) && !empty($tps['customer_count_from'])) {
                                                    $paxArr[] = (int)$tps['customer_count_from'];
                                                }

                                                if (isset($tps['customer_count_to'])) {
                                                    $paxArr[] = (int)$tps['customer_count_to'];
                                                }
                                            }
                                        }

                                    }


                                    // 자상품 옵션
                                    if (isset($tsp['tour_options']) && ! empty($tsp['tour_options'])) {
                                        foreach ($tsp['tour_options'] as $to) {

                                            if ( ! isset($to['tour_fields']) || empty($to['tour_fields'])) {
                                                continue;
                                            }

                                            // 옵션 날짜 비교
                                            $optDateFrom = date('Y-m-d', strtotime($to['option_date_from']));
                                            $optDateTo = date('Y-m-d', strtotime($to['option_date_to']));
                                            $optWeek = date('w');
                                            $optWeeks = explode( ',', $to['option_week']);

                                            // @todo 날짜별 옵션처리는 ??
                                            if (false === $this->_validRangeDate($optDateFrom, $optDateTo)) {
                                                continue;
                                            }

                                            //if (false === (in_array($optWeek, $optWeeks))) continue;
                                            // @todo 날짜별 옵션처리는 ??

                                            foreach ($to['tour_fields'] as $tf) {

                                                $tmpOpt = [];
                                                $tmpOpt['id'] = $tf['field_id'];
                                                $tmpOpt['target'] = TFConst::TS_OPTION_TARGET_PER_BOOK;
                                                $tmpOpt['name'] = $tf['field_ko'] ?? ($tf['field_label'] ?? '');
                                                $tmpOpt['type'] = (isset($tf['tour_items']) && ! empty($tf['tour_items'])) ? TFConst::TS_OPTION_TYPE_LIST : TFConst::TS_OPTION_TYPE_TEXT;
                                                $tmpOpt['required'] = (isset($tf['tour_items']) && ! empty($tf['tour_items'])) ? true : false;
                                                $tmpOpt['price'] = 0;

                                                if (isset($tf['tour_items']) && ! empty($tf['tour_items']) && is_array($tf['tour_items'])) {
                                                    $tmpOpt['item'] = [];
                                                    foreach ($tf['tour_items'] as $fl) {
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

                        } elseif (true === $transferItemFlag) {

                            $tourItem = $product['transfer_item'];

                            $productDetail['productTypeKey'] = $tourItem['transfer_item_idx'];

                            $productDetail['title'] = $tourItem['transfer_ko'];
                            $productDetail['title'] = $this->_replaceSpecialChar($productDetail['title']);

                            $productDetail['durationDays'] = 0;
                            $productDetail['durationHours'] = 0;
                            $productDetail['durationMins'] = 0;

                            $productDetail['minAdultAge'] = 0;
                            $productDetail['maxAdultAge'] = 99;
                            $productDetail['hasChildPrice'] = false;
                            $productDetail['minChildAge'] = 0;

                            $priceChildArr = $priceTeenArr = [0];
                            $paxArr = [1];

                            if (isset($tourItem['transfer_suppliers'])) {
                                foreach ($tourItem['transfer_suppliers'] as $tsp) {

                                    // 바우처 정보
                                    $voucherUse .= (!empty($tsp['voucher_content'])) ? $tsp['voucher_content'] : $tsp['note_content'];

                                    // 환불규정
                                    $refundPolicy = $tsp['cancel_content'] ?? '';

                                    // 환불 불가여부
                                    $isNoneRefund = (isset($tsp['cancel_type_cd']) && $tsp['cancel_type_cd'] == 'refund') ? false : true;

                                    // 불/포함사항
                                    $inExClude .= (!empty($tsp['include_content'])) ? '포함사항' . "\r\n" . $this->_replaceSpecialChar($tsp['include_content']) . "\r\n\r\n" : '';
                                    $inExClude .= (!empty($tsp['exclude_content'])) ? '불포함사항' . "\r\n" . $this->_replaceSpecialChar($tsp['exclude_content']) : '';

                                    // 최대/최소 가격
                                    $tmpItem = [];

                                    // 날짜 검색이면
                                    if (true === $dateSearchFlag) {

                                        $adultIdtTitle = TFConst::TF_TRANSFER_LABEL;

                                        if ( ! empty($pDate)) {
                                            foreach ($pDate as $sDate) {

                                                $availAbleDate = [];
                                                $tmpDate = date('Y-m-d', strtotime($sDate));
                                                $tmpWeek = date('w', strtotime($sDate));

                                                foreach ($tsp['transfer_seasons'] as $ts) {
                                                    $weeks = explode(',', $ts['season_week']);
                                                    $tourDateFrom = date('Y-m-d', strtotime($ts['season_date_from']));
                                                    $tourDateTo = date('Y-m-d', strtotime($ts['season_date_to']));
                                                    $tourSaleDateTo = date('Y-m-d', strtotime($ts['season_date_to'] . ' -' . $ts['sale_day_to'] . ' days'));

                                                    if ($tmpDate >= $tourDateFrom && $tmpDate <= $tourSaleDateTo && (in_array($tmpWeek, $weeks))) {
                                                        $availAbleDate[] = true;
                                                        foreach ($ts['transfer_prices'] as $tps) {
                                                            $priceArr[] = $priceAdultArr[] = ($tps['sale_price'] ?? 0);
                                                        }
                                                    }
                                                }

                                                $saleAvailableTour[] = [
                                                    'from_to'         => $tmpDate,
                                                    'available'       => (!empty($availAbleDate)),
                                                    'adult_price'     => ((!empty($availAbleDate)) ? (int)(min(($priceAdultArr ?? 0))) : 0),
                                                    'senior_price'    => 0,
                                                    'teen_price'      => 0,
                                                    'child_price'     => 0,
                                                    'infant_price'    => 0,
                                                ];
                                            }
                                        }

                                    } else {

                                        $transeAllPrices = [];
                                        foreach ($tsp['transfer_seasons'] as $ts) {

                                            if ( ! isset($ts['transfer_prices']) || empty($ts['transfer_prices'])) {
                                                continue;
                                            }

                                            $tourDateFrom = date('Y-m-d', strtotime($ts['season_date_from']));
                                            $tourDateTo = date('Y-m-d', strtotime($ts['season_date_to']));
                                            $tourSaleDateTo = date('Y-m-d',
                                                strtotime($ts['season_date_to'] . ' -' . $ts['sale_day_to'] . ' days'));
                                            $weeks = explode( ',', $ts['season_week']);

                                            if (true === $this->_validRangeDate($tourDateFrom, $tourDateTo)) {

                                                $transePrices = array_column($ts['transfer_prices'], 'sale_price');
                                                $transeAllPrices = array_merge($transeAllPrices, $transePrices);
                                                $minTransePrice = min($transeAllPrices);

                                                foreach ($ts['transfer_prices'] as $tps) {

                                                    $priceArr[] = $priceAdultArr[] = (int)($tps['sale_price'] ?? 0);

                                                    if (isset($tps['customer_count_to'])) {
                                                        // 픽업상품은 1로 고정
                                                        //$paxArr[] = (int)$tps['customer_count_to'];
                                                        $paxArr[] = 1;
                                                    }

                                                    $tmpItem[] = [
                                                        'label' => $tps['vehicle_type_ko'] . '-' . $tps['customer_count_to'] . '인',
                                                        'value' => $tps['transfer_price_idx'],
                                                        'price' => (int)($tps['sale_price'] - $minTransePrice)
                                                    ];

                                                }
                                            }

                                        } // end foreach

                                    }


                                    // 픽업 서비스인 경우 옵션에 상품정보를 넣는다.
                                    $tourOptions[] = [
                                        'id'       => 'transfer_price_idx',
                                        'target'   => TFConst::TS_OPTION_TARGET_PER_BOOK,
                                        'name'     => '차량선택',
                                        'type'     => TFConst::TS_OPTION_TYPE_LIST,
                                        'required' => true,
                                        'item'     => $tmpItem,
                                    ];

                                    // 자상품 옵션
                                    if (isset($tsp['transfer_option']['transfer_fields']) && ! empty($tsp['transfer_option']['transfer_fields'])) {

                                        $tFields = $tsp['transfer_option']['transfer_fields'];

                                        foreach ($tFields as $tf) {

                                            $tmpOpt = [];
                                            $tmpOpt['id'] = $tf['field_id'];
                                            $tmpOpt['target'] = TFConst::TS_OPTION_TARGET_PER_BOOK;
                                            $tmpOpt['name'] = $tf['field_ko'] ?? ($tf['field_label'] ?? '');
                                            $tmpOpt['type'] = (isset($tf['transfer_items']) && ! empty($tf['transfer_items'])) ? 1 : 4;
                                            $tmpOpt['required'] = true;
                                            $tmpOpt['price'] = 0;

                                            if (isset($tf['transfer_items']) && ! empty($tf['transfer_items']) && is_array($tf['transfer_items'])) {
                                                $tmpOpt['item'] = [];
                                                foreach ($tf['transfer_items'] as $fl) {
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

                        $productDetail['minPax'] = ( ! empty($paxArr)) ? min($paxArr) : 0;
                        $productDetail['maxPax'] = ( ! empty($paxArr)) ? max($paxArr) : 0;

                        $tourAddOpts = [
                            [
                                'id'       => 'participant_sex',
                                'target'   => TFConst::TS_OPTION_TARGET_PER_PAX,
                                'name'     => '성별',
                                'type'     => TFConst::TS_OPTION_TYPE_LIST,
                                'required' => true,
                                'price'    => 0,
                                'item'     => [
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
                                'id'       => 'participant_name',
                                'target'   => TFConst::TS_OPTION_TARGET_PER_PAX,
                                'name'     => '성/이름',
                                'type'     => TFConst::TS_OPTION_TYPE_TEXT,
                                'required' => true,
                                'price'    => 0
                            ],
                            [
                                'id'       => 'participant_birth',
                                'target'   => TFConst::TS_OPTION_TARGET_PER_PAX,
                                'name'     => '생년월일',
                                'type'     => TFConst::TS_OPTION_TYPE_DATE,
                                'required' => true,
                                'price'    => 0
                            ],
                            [
                                'id'       => 'participant_nation',
                                'target'   => TFConst::TS_OPTION_TARGET_PER_PAX,
                                'name'     => '국가',
                                'type'     => TFConst::TS_OPTION_TYPE_TEXT,
                                'required' => false,
                                'price'    => 0
                            ]
                        ];

                        $tourOptions = array_merge($tourOptions, $tourAddOpts);

                        $productDetail['description'] = $inExClude ?? '';
                        $productDetail['hasSeniorPrice'] = $productDetail['hasSeniorPrice'] ?? false;
                        $productDetail['hasTeenagerPrice'] = $productDetail['hasTeenagerPrice'] ?? false;
                        $productDetail['minTeenagerAge'] = $productDetail['minTeenagerAge'] ?? 0;
                        $productDetail['allowInfants'] = $productDetail['allowInfants'] ?? false;
                        $productDetail['maxInfantAge'] = $productDetail['maxInfantAge'] ?? 0;
                        $productDetail['instantConfirmation'] = false;
                        $productDetail['voucherType'] = 'E_VOUCHER';
                        $productDetail['voucherUse'] = strip_tags($this->_replaceSpecialChar($voucherUse));
                        //$productDetail['meetingTime'] = $tourItem[''];
                        //$productDetail['meetingLocation'] = $tourItem[''];
                        $productDetail['isNonRefundable'] = $isNoneRefund;
                        $productDetail['refundPolicy'] = $this->_replaceSpecialChar($refundPolicy);
                        //$productDetail['validityType'] = $tourItem[''];
                        //$productDetail['validityDate'] = $tourItem[''];
                        $productDetail['minPrice'] = ( ! empty($priceAdultArr)) ? (int)min($priceAdultArr) : 0;
                        $productDetail['options'] = $tourOptions;

                        // 날짜검색이라면 데이터 초기화
                        if (true === $dateSearchFlag) {

                            $teenLabel = TFConst::TF_TEENAGE_LABEL;
                            $childLabel = TFConst::TF_CHILD_LABEL;
                            $infantLabel = TFConst::TF_INFANT_LABEL;
                            $babyLavel = TFConst::TS_INFANT_LABEL;

                            if (!empty($maxTeenAge) && !empty($minTeenAge)) {
                                $teenLabel .= ' (만 '. $minTeenAge .'세 ~ '. $maxTeenAge .'세)';
                            }

                            if (!empty($maxChildAge) && !empty($minChildAge)) {
                                $childLabel .= ' (만 '. $minChildAge .'세 ~ '. $maxChildAge .'세)';
                            }

                            if (!empty($maxInfantPriceAge)) {
                                $infantLabel .= ' (만 '. ($minInfantPriceAge ?? 0) .'세 ~ '. $maxInfantPriceAge .'세)';
                            }

                            if ($productDetail['allowInfants'] === true) {
                                $babyLavel .= ' (만 ' . $productDetail['maxInfantAge'] . '세 이하)';
                            }

                            $productDetail = [
                                'adultIdtTitle'    => $adultIdtTitle ?? TFConst::TF_ADULT_LABEL . ($adultSubLabel ?? ''),
                                'seniorIdtTitle'   => (true === $productDetail['hasSeniorPrice']) ? $teenLabel . ($seniorSubLabel ?? '') : '',
                                'teenagerIdtTitle' => (true === $productDetail['hasTeenagerPrice']) ? $childLabel . ($teenSubLabel ?? '') : '',
                                'childIdtTitle'    => (true === ($hasChild ?? false)) ? $infantLabel . ($childSubLabel ?? '') : '',
                                'infantIdtTitle'   => (true === ($hasBaby ?? false)) ? $babyLavel . ($infantSubLabel ?? '') : '',
                            ];
                        }

                        if (empty($priceAdultArr)) $priceAdultArr = [0];
                        if (empty($priceChildArr)) $priceChildArr = [0];
                        if (empty($priceTeenArr))  $priceTeenArr = [0];
                        if (empty($priceInfantArr))  $priceInfantArr = [0];
                        if (empty($priceBabyArr))  $priceBabyArr = [0];

                        // 검색 날짜내에 가능한 상품이 있으면
                        if ( ! empty($saleAvailableTour)) {
                            foreach ($saleAvailableTour as $st) {
                                $productDetail['priceData'][] = [
                                    'date'      => $st['from_to'],
                                    'available' => $st['available'],
                                    'price'     => [
                                        'adult'    => $st['adult_price'] ?? (int)max($priceAdultArr),
                                        'senior'   => $st['senior_price'] ?? (int)max($priceTeenArr),
                                        'teenager' => $st['teen_price'] ?? (int)max($priceChildArr),
                                        'child'    => $st['child_price'] ?? (int)max($priceInfantArr),
                                        'infant'   => $st['infant_price'] ?? (int)max($priceBabyArr),
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

                        // 투어상품
                        if (isset($product['tour_items']) && is_array($product['tour_items'])) {
                            foreach ($product['tour_items'] as $row) {

                                if (!isset($row['tour_suppliers']) || empty($row['tour_suppliers'])) {
                                    continue;
                                }

                                $availAble = [];
                                foreach ($row['tour_suppliers'] as $tsp) {
                                    foreach ($tsp['tour_seasons'] as $ts) {

                                        if (false === $this->_validRangeDate($ts['season_date_from'], $ts['season_date_to'])) {
                                            continue;
                                        }

                                        if (! isset($ts['tour_prices']) || empty($ts['tour_prices'])) {
                                            continue;
                                        }

                                        $availAble[] = $row['tour_item_idx'];

                                    }
                                }

                                if (!empty($availAble)) {
                                    $productDetail[] = [
                                        'productTypeKey' => (int)$row['tour_item_idx'],
                                        'title'          => $this->_replaceSpecialChar($row['tour_ko']),
                                    ];
                                }

                            }

                            // 픽업상품
                        } elseif (isset($product['transfer_items']) && is_array($product['transfer_items'])) {
                            foreach ($product['transfer_items'] as $row) {

                                $availAble = [];
                                if (!isset($row['transfer_suppliers']) || empty($row['transfer_suppliers'])) {
                                    continue;
                                }

                                foreach ($row['transfer_suppliers'] as $tsp) {
                                    foreach ($tsp['transfer_seasons'] as $ts) {

                                        if (false === $this->_validRangeDate($ts['season_date_from'], $ts['season_date_to'])) {
                                            continue;
                                        }

                                        if (! isset($ts['transfer_prices']) || empty($ts['transfer_prices'])) {
                                            continue;
                                        }

                                        $availAble[] = $row['transfer_item_idx'];
                                    }
                                }

                                if (!empty($availAble)) {
                                    $productDetail[] = [
                                        'productTypeKey' => (int)$row['transfer_item_idx'],
                                        'title'          => $this->_replaceSpecialChar($row['transfer_ko']),
                                    ];
                                }

                            }
                        }

                        break;

                    // 모상품 상세
                    default :

                        $desc = $product['description_content'] ?? null;
                        $desc = $this->_replaceSpecialChar($desc);

                        $productTitle = $product['product_ko'] ?? '';
                        $productDetail = [
                            'productKey'     => (int)$product['product_idx'],
                            'updatedAt'      => $product['modify_datetime'] ?? null,
                            'title'          => $this->_replaceSpecialChar($productTitle),
                            'description'    => $desc ?? null,
                            'highlights'     => $product['highlight_content'] ?? null,
                            'additionalInfo' => ($this->_replaceSpecialChar($product['comment_content'])) ?? null,
                        ];

                        // 여정
                        $productDetail['itinerary'] = '';
                        if ( ! empty($product['tour_itineraries']) && is_array($product['tour_itineraries'])) {
                            foreach ($product['tour_itineraries'] as $seq => $ti) {
                                $productDetail['itinerary'] .= ''
                                    . '<div class="supplier_itinerarys" seq="'. $seq .'">'
                                    . '<span class="supplier_itinerary_daytime">' . ($ti['itinerary_daytime'] ?? null) . '</span>'
                                    . '<span class="supplier_itinerary_route">' . ($ti['itinerary_route'] ?? null) . '</span>'
                                    . '<div class="supplier_itinerary_content">' . ($ti['itinerary_content'] ?? null) . '</div>'
                                    . '</div>'
                                ;
                            }

                            $productDetail['itinerary'] = $this->_replaceSpecialChar($productDetail['itinerary']);
                        }

                        $productDetail['latitude'] = 0;
                        $productDetail['longitude'] = 0;
                        if (isset($product['tour_maps'][0]) && ! empty($product['tour_maps'][0])) {
                            $productDetail['latitude'] = (float)$product['tour_maps'][0]['point_latitude'];
                            $productDetail['longitude'] = (float)$product['tour_maps'][0]['point_longitude'];
                        }

                        $productDetail['currency'] = 'KRW';

                        // 투액상품 최소가
                        $priceArr = [];
                        if ( ! empty($product['tour_items']) && is_array($product['tour_items'])) {
                            foreach ($product['tour_items'] as $ti) {

                                if ( ! isset($ti['tour_suppliers']) || empty($ti['tour_suppliers'])) {
                                    continue;
                                }

                                foreach ($ti['tour_suppliers'] as $tsp) {
                                    foreach ($tsp['tour_seasons'] as $ts) {

                                        if ( ! isset($ts['tour_prices']) || empty($ts['tour_prices'])) {
                                            continue;
                                        }

                                        // 가격에도 날짜가 있다
                                        if (false === $this->_validRangeDate($ts['season_date_from'], $ts['season_date_to'])) {
                                            continue;
                                        }

                                        // 최소가는 성인만
                                        if (false !== ($tpIdx = array_search(TFConst::TF_ADULT_LABEL, array_column($ts['tour_prices'], 'sale_unit_ko')))) {

                                            if (!empty($ts['tour_prices'][$tpIdx]['sale_price'])) {
                                                $priceArr[] =  $ts['tour_prices'][$tpIdx]['sale_price'];
                                            }

                                            if (!empty($ts['tour_prices'][$tpIdx]['customer_count_from'])) {
                                                $paxArr[] =  $ts['tour_prices'][$tpIdx]['customer_count_from'];
                                            }

                                            if (!empty($ts['tour_prices'][$tpIdx]['customer_count_to'])) {
                                                $paxArr[] =  $ts['tour_prices'][$tpIdx]['customer_count_to'];
                                            }


                                        }

                                    }
                                }
                            }
                        }

                        // 픽업상품 최소가
                        if ( ! empty($product['transfer_items']) && is_array($product['transfer_items'])) {

                            $paxArr[] =  1;
                            foreach ($product['transfer_items'] as $ti) {

                                if ( ! isset($ti['transfer_suppliers']) || empty($ti['transfer_suppliers'])) {
                                    continue;
                                }

                                foreach ($ti['transfer_suppliers'] as $tsp) {
                                    foreach ($tsp['transfer_seasons'] as $ts) {

                                        // 가격에도 날짜가 있다
                                        if (false === $this->_validRangeDate($ts['season_date_from'], $ts['season_date_to'])) {
                                            continue;
                                        }

                                        if ( ! isset($ts['transfer_prices']) || empty($ts['transfer_prices'])) {
                                            continue;
                                        }

                                        foreach ($ts['transfer_prices'] as $tps) {
                                            if ($tps['sale_price'] > 0) {
                                                $priceArr[] = (int)$tps['sale_price'];
                                            }
                                        }
                                    }
                                }
                            }
                        }

                        $productDetail['minPrice'] = ( ! empty($priceArr)) ? ((int)min($priceArr)) : 0;
                        $productDetail['minPax'] = ( ! empty($paxArr)) ? min($paxArr) : 0;
                        $productDetail['minPax'] = (int)($productDetail['minPax']);
                        $productDetail['maxPax'] = ( ! empty($paxArr)) ? max($paxArr) : 0;
                        $productDetail['maxPax'] = (int)($productDetail['maxPax']);


                        $redisKey = TFConst::REDIS_TF_PRODUCT_REDIS_KEY . '_' . $product['product_idx'];
                        if (false !== ($productRedis = RedisUtil::getData($this->redis, $redisKey, $this->redisSession))) {
                            $productDetail['location'] = TFConst::TS_AREA_CODE[$productRedis['area_idx']][TFConst::TS_AREA_LOC_TXT] ?? null;
                            $productDetail['city'] = TFConst::TS_AREA_CODE[$productRedis['area_idx']][TFConst::TS_AREA_CITY_TXT] ?? null;
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

                if (!empty($params['product'])) {
                    $body['product_idx'] = $params['product']['product_idx'] ?? null;
                    unset($params['product']);
                }

                $body['params'] = $params;
                $bodyJson = json_encode($body, JSON_UNESCAPED_UNICODE);
                $errMessage = CommonUtil::getErrorMessage($ex);
                $results = [
                    'code' => ErrorConst::FAIL_CODE,
                    'message' => $this->_getErrorMessage($ex, true, ['subject' => ' 상품조회 에러', 'body' => '('. $errMessage .') - ' . $bodyJson]),
                ];

                try {
                    if ($ex->getCode() == ErrorConst::ERROR_RDB_NO_DATA_EXIST) {
                        $data = [
                            'productKey'     => $body['product_idx'],
                            'productTypeKey' => $body['params']['productTypeKey'] ?? null,
                            'updateType'     => 'DELETE',
                            'updateAt'       => date('Y-m-d H:i:s')
                        ];
                        $this->_passthru($data);
                    }
                } catch (\Exception $e) {
                    LogMessage::error($e->getMessage());
                }
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
        public function setProductsBatch(Request $request, Response $response, $args)
        {
            $products = null;

            $batchProducts = [];
            $productDetail = [];

            $startDate = CommonUtil::getDateTime();
            $startTime = CommonUtil::getMicroTime();
            $startTimeStamp = $startDate . ' ' . $startTime;

            $updatedProduct = []; // 업데이트 웹훅 배열
            $excute = true;
            $today = date('Y-m-d');

            try {

                $params = $request->getAttribute('params');

                if ( ! isset($params['execute']) || empty($params['execute'])) {
                    $excute = false;
                    throw new \Exception('Batch Non-excute');
                }

                // 지역 정보
                $redisKey = TFConst::REDIS_TF_AREA_REDIS_KEY;
                $targetUrl = TFConst::TF_URL . TFConst::TF_GET_AREA_URI;
                $eventKey = $this->_getRedisEventKey(['action'=>'batch_get_areas', 'datetime'   => $startTimeStamp]);
                $this->guzzleOpt['query'] = ['event_key' => $eventKey];
                $areaResult = Http::httpRequest($targetUrl, $this->guzzleOpt);

                if (!empty($areaResult['errors'])) {
                    LogMessage::error(json_encode($areaResult['errors'], JSON_UNESCAPED_UNICODE));
                    throw new \Exception('Batch Area Error!!');
                }

                $areas = $areaResult['areas'];
                $this->_setRedis($redisKey, $areas);

                foreach ($areas as $row) {

                    // 지역별 모상품 리스트
                    $areaIdx = $row['area_idx'];
                    $redisKey = TFConst::REDIS_TF_PRODUCTS_REDIS_KEY . '_' . $areaIdx;

                    if ( ! array_key_exists($areaIdx, TFConst::TS_AREA_CODE)) {
                        LogMessage::error('NOT EXIST TF area_idx :: ' . $areaIdx);
                        CommonUtil::sendSlack('NOT EXIST TF area_idx :: ' . $areaIdx);
                        continue;
                    }

                    LogMessage::info('TF area_idx :: ' . $areaIdx);

                    $eventKey = $this->_getRedisEventKey(['action' => 'batch_get_products', 'area_idx' => $areaIdx, 'datetime' => $startTimeStamp]);
                    $this->guzzleOpt['query'] = ['event_key' => $eventKey];
                    $targetUrl = TFConst::TF_URL . TFConst::TF_GET_AREA_URI . '/' . $areaIdx . '/products';
                    $areaProducts = Http::httpRequest($targetUrl, $this->guzzleOpt);

                    if (!empty($areaProducts['errors'])) {
                        LogMessage::error($targetUrl);
                        LogMessage::error(json_encode($areaProducts['errors'], JSON_UNESCAPED_UNICODE));
                        continue;
                    }

                    $areaProducts = $areaProducts['products'];
                    $this->_setRedis($redisKey, $areaProducts);

                    try {

                        // 지역별 모상품 리스트 루프
                        foreach ($areaProducts as $aps) {

                            $productIdx = $aps['product_idx'];

                            // 제외할 상품목록
                            if (in_array($productIdx, $this->exceptProductKeys)) {
                                continue;
                            }

                            // 모상품 상세정보
                            LogMessage::info('TF product_idx :: ' . $productIdx);

                            $redisKey = TFConst::REDIS_TF_PRODUCT_REDIS_KEY . '_' . $productIdx;
                            $targetUrl = TFConst::TF_URL . TFConst::TF_GET_PRODUCTS_URI . '/' . $productIdx;

                            // 이전 데이터
                            $productDetailOld = RedisUtil::getData($this->redis, $redisKey, $this->redisSession);

                            $eventKey = $this->_getRedisEventKey(['action'=>'batch_get_product', 'product_idx' => $productIdx, 'datetime' => $startTimeStamp]);
                            $this->guzzleOpt['query'] = ['event_key' => $eventKey];
                            $productDetail = Http::httpRequest($targetUrl, $this->guzzleOpt);

                            // 에러면
                            if (!empty($productDetail['errors'])) {
                                LogMessage::error($targetUrl);
                                LogMessage::error(json_encode($productDetail['errors'], JSON_UNESCAPED_UNICODE));
                                continue;
                            }

                            // 상품정보가 없으면
                            if (! isset($productDetail['product']) || empty($productDetail['product'])) {
                                $updatedProduct[] = [
                                    'productKey' => $productIdx,
                                    'updateType' => 'DELETE',
                                    'updateAt'   => $productDetail['modify_datetime'],
                                ];
                                RedisUtil::delData($this->redis, $redisKey, TFConst::REDIS_TF_SESSION);
                                continue;
                            }

                            $productDetail = $productDetail['product'];
                            $productDetail['area_idx'] = $areaIdx;

                            // 투어상품 상세정보
                            if (isset($productDetail['tour_items']) && is_array($productDetail['tour_items'])) {
                                $tourSuppliers = [];

                                foreach ($productDetail['tour_items'] as $ti) {

                                    if (!isset($ti['tour_suppliers']) || empty($ti['tour_suppliers'])) {
                                        continue;
                                    }

                                    foreach ($ti['tour_suppliers'] as $tsp) {
                                        foreach ($tsp['tour_seasons'] as $ts) {

                                            // 가격정보
                                            if (!isset($ts['tour_prices']) || empty($ts['tour_prices'])) {
                                                continue;
                                            }

                                            // 상품날짜
                                            if (false === $this->_validRangeDate($ts['season_date_from'], $ts['season_date_to'], $today)) {
                                                continue;
                                            }

                                            // 성인 상품가격이 0이면 목록에서 제외
                                            if (false !== ($tpIdx = array_search(TFConst::TF_ADULT_LABEL, array_column($ts['tour_prices'], 'sale_unit_ko')))) {
                                                if (empty($ts['tour_prices'][$tpIdx]['sale_price'])) continue;
                                            }

                                            $tourSuppliers[] = true;
                                        }
                                    }
                                }
                            }

                            // 픽업상품 상세정보
                            if (isset($productDetail['transfer_items']) && is_array($productDetail['transfer_items'])) {
                                $tourSuppliers = [];
                                foreach ($productDetail['transfer_items'] as $ti) {

                                    if ( ! isset($ti['transfer_suppliers']) || empty($ti['transfer_suppliers'])) {
                                        continue;
                                    }

                                    foreach ($ti['transfer_suppliers'] as $tsp) {
                                        foreach ($tsp['transfer_seasons'] as $ts) {
                                            if (false === $this->_validRangeDate($ts['season_date_from'], $ts['season_date_to'], $today)) {
                                                continue;
                                            }
                                            $tourSuppliers[] = true;
                                        }
                                    }
                                }
                            }

                            if (empty($tourSuppliers)) {
                                $updatedProduct[] = [
                                    'productKey' => $productIdx,
                                    'updateType' => 'DELETE',
                                    'updateAt'   => $productDetail['modify_datetime'],
                                ];
                                RedisUtil::delData($this->redis, $redisKey, TFConst::REDIS_TF_SESSION);
                                continue;
                            }

                            try {

                                // 이전 데이터와 날짜 빅교
                                if (false !== $productDetailOld) {
                                    if ($productDetailOld['modify_datetime'] <> $productDetail['modify_datetime']) {
                                        $updatedProduct[] = [
                                            'productKey' => $productIdx,
                                            'updateType' => 'UPDATE',
                                            'updateAt'   => $productDetail['modify_datetime'],
                                        ];
                                    }
                                }

                                // 이전 데이터가 없다면 데이터 추가
                                if (false === $productDetailOld) {
                                    LogMessage::error('Product New Data :: productKey - ' . $productIdx . ', area - ' . $areaIdx);
                                    $updatedProduct[] = [
                                        'productKey' => $productIdx,
                                        'updateType' => 'ADD',
                                        'updateAt'   => $productDetail['modify_datetime'],
                                    ];
                                }

                            } catch (\Exception $eee) {
                                LogMessage::error('modify_datetime diff error');
                                LogMessage::info('product :: ' . $productIdx . ' - date :: ' . ($productDetail['modify_datetime'] ?? null));
                            }

                            $this->_setRedis($redisKey, $productDetail);

                            try {

                                $tourItemIdx = 0;
                                $productSubDetail = null;
                                $productSubDetailArray = [];

                                // 투어상품 상세정보
                                if (isset($productDetail['tour_items']) && is_array($productDetail['tour_items'])) {
                                    foreach ($productDetail['tour_items'] as $ti) {

                                        if (!isset($ti['tour_suppliers']) || empty($ti['tour_suppliers'])) {
                                            continue;
                                        }

                                        $tourItemIdx = $ti['tour_item_idx'];
                                        LogMessage::info('TF tour_item_idx :: ' . $tourItemIdx);

                                        $productSubDetail = [
                                            'area_idx'    => $areaIdx,
                                            'product_idx' => $productIdx,
                                            'tour_item'   => $ti
                                        ];

                                        $productSubDetailArray[] = $productSubDetail;

                                        $redisKey = TFConst::REDIS_TF_SUB_PRODUCT_REDIS_KEY . '_' . $tourItemIdx;
                                        $this->_setRedis($redisKey, $productSubDetail);
                                    }
                                }

                                // 픽업상품 상세정보
                                if (isset($productDetail['transfer_items']) && is_array($productDetail['transfer_items'])) {
                                    foreach ($productDetail['transfer_items'] as $ti) {

                                        if (!isset($ti['transfer_suppliers']) || empty($ti['transfer_suppliers'])) {
                                            continue;
                                        }

                                        $tourItemIdx = $ti['transfer_item_idx'];
                                        LogMessage::info('TF transfer_item_idx :: ' . $tourItemIdx);

                                        $productSubDetail = [
                                            'area_idx'      => $areaIdx,
                                            'product_idx'   => $productIdx,
                                            'transfer_item' => $ti
                                        ];

                                        $productSubDetailArray[] = $productSubDetail;

                                        $redisKey = TFConst::REDIS_TF_SUB_PRODUCT_REDIS_KEY . '_' . $tourItemIdx;
                                        $this->_setRedis($redisKey, $productSubDetail);
                                    }
                                }

                            } catch (\Exception $ee) {
                                LogMessage::error('SubProduct Detail Error :: ' . $areaIdx . '-' . $productIdx . '-' . $tourItemIdx);
                                LogMessage::error($ee->getMessage());
                                LogMessage::info(json_encode($tourItemIdx, JSON_UNESCAPED_UNICODE));
                            }

                            if (!array_key_exists($productIdx, $batchProducts) && !empty($productSubDetailArray)) {
                                $batchProducts[$productIdx] = $productIdx;
                                $products[] = [
                                    'productKey' => $productIdx,
                                    'updatedAt'  => $productDetail['modify_datetime'] ?? null,
                                    'title'      => $this->_replaceSpecialChar($aps['product_ko'])
                                ];
                            }

                            sleep(1);

                        }

                    } catch (\Exception $e) {
                        LogMessage::error('Product Detail Error :: ' . $productIdx . '_' . $areaIdx);
                        LogMessage::error($e->getMessage());
                        LogMessage::info(json_encode($productDetail, JSON_UNESCAPED_UNICODE));
                    }

                    sleep(1);

                }

                // Redis 덮어 쓴다.
                $this->_setRedis(TFConst::REDIS_TF_PRODUCTS_REDIS_KEY, $products);
                $results = $products;

                // 비즈의 Response Redis Data 삭제
                RedisUtil::flush($this->redis, CommonConst::REDIS_BIZ_RES_SESSION);

            } catch (\Exception $ex) {
                $results = [
                    'result' => ErrorConst::FAIL_STRING,
                    'data'   => [
                        'message' => $this->_getErrorMessage($ex),
                    ]
                ];
            }

            $endTime = CommonUtil::getMicroTime();

            if (true === $excute) {
                LogMessage::info('TF Batch Start :: ' . $startTimeStamp);
                LogMessage::info('TF Batch End :: ' . CommonUtil::getDateTime() . ' ' . $endTime);
                LogMessage::info('TF Batch Runtime :: ' . ($endTime - $startTime));
                LogMessage::info('TF Update product count :: ' . count($updatedProduct));
                CommonUtil::sendSlack('['. APP_ENV .'] TF Batch Start :: ' . $startTimeStamp . ', TF Batch End :: ' . CommonUtil::getDateTime() . ' ' . $endTime, 'Batch Bot');
            }

            // 업데이트 웹훅
            if (APP_ENV === APP_ENV_STAGING || APP_ENV === APP_ENV_PRODUCTION) {
                if (!empty($updatedProduct)) {
                    try {
                        sleep(5);
                        foreach ($updatedProduct as $key => $data) {
                            try {
                                $this->_passthru($data);
                                sleep(1);
                            } catch (\Exception $ex) {
                                LogMessage::error('Product TS Webhook Error :: ' . $data['productKey'] . ' - ' . $ex->getMessage());
                            }
                        }

                    } catch (\Exception $eex) {
                        LogMessage::error('Product Webhook Error');
                    }
                }
            }

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
            $results = [];

            $startDate = CommonUtil::getDateTime();
            $startTime = CommonUtil::getMicroTime();
            $startTimeStamp = $startDate . ' ' . $startTime;

            $updatedProduct = []; // 업데이트 웹훅 배열
            $today = date('Y-m-d');

            try {

                if (false === CommonUtil::validateParams($args, ['product_idx', 'area_idx'], true)) {
                    throw new \Exception(null, ErrorConst::ERROR_NOT_FOUND_REQUIRE_FIELD);
                }

                $eventKey = $this->_getRedisEventKey(['action' => 'batch_get_products', 'area_idx' => $areaIdx, 'datetime' => $startTimeStamp]);
                $this->guzzleOpt['query'] = ['event_key' => $eventKey];
                $targetUrl = TFConst::TF_URL . TFConst::TF_GET_AREA_URI . '/' . $areaIdx . '/products';
                $areaProducts = Http::httpRequest($targetUrl, $this->guzzleOpt);

                if (false === (array_search($productIdx, array_column($areaProducts['products'], 'product_idx')))) {
                    throw new \Exception('', ErrorConst::ERROR_RDB_NO_DATA_EXIST);
                }

                $redisKey = TFConst::REDIS_TF_PRODUCT_REDIS_KEY . '_' . $productIdx;
                $targetUrl = TFConst::TF_URL . TFConst::TF_GET_PRODUCTS_URI . '/' . $productIdx;

                $eventKey = $this->_getRedisEventKey(['action'=>'batch_get_product', 'product_idx' => $productIdx, 'datetime' => $startTimeStamp]);
                $this->guzzleOpt['query'] = ['event_key' => $eventKey];
                $productDetail = Http::httpRequest($targetUrl, $this->guzzleOpt);

                // 에러면
                if (!empty($productDetail['errors'])) {
                    LogMessage::error($targetUrl);
                    LogMessage::error(json_encode($productDetail['errors'], JSON_UNESCAPED_UNICODE));
                    throw new \Exception('', ErrorConst::ERROR_RDB_NO_DATA_EXIST);
                }

                if (! isset($productDetail['product']) || empty($productDetail['product'])) {
                    throw new \Exception('', ErrorConst::ERROR_RDB_NO_DATA_EXIST);
                }

                $productDetail = $productDetail['product'];
                $productDetail['area_idx'] = $areaIdx;

                // 투어상품 상세정보
                if (isset($productDetail['tour_items']) && is_array($productDetail['tour_items'])) {
                    $tourSuppliers = [];

                    foreach ($productDetail['tour_items'] as $ti) {

                        if (!isset($ti['tour_suppliers']) || empty($ti['tour_suppliers'])) {
                            continue;
                        }

                        foreach ($ti['tour_suppliers'] as $tsp) {
                            foreach ($tsp['tour_seasons'] as $ts) {

                                // 가격정보
                                if (!isset($ts['tour_prices']) || empty($ts['tour_prices'])) {
                                    continue;
                                }

                                // 상품날짜
                                if (false === $this->_validRangeDate($ts['season_date_from'], $ts['season_date_to'], $today)) {
                                    continue;
                                }

                                // 성인 상품가격이 0이면 목록에서 제외
                                if (false !== ($tpIdx = array_search(TFConst::TF_ADULT_LABEL, array_column($ts['tour_prices'], 'sale_unit_ko')))) {
                                    if (empty($ts['tour_prices'][$tpIdx]['sale_price'])) continue;
                                }

                                $tourSuppliers[] = true;
                            }
                        }
                    }
                }

                // 픽업상품 상세정보
                if (isset($productDetail['transfer_items']) && is_array($productDetail['transfer_items'])) {
                    $tourSuppliers = [];
                    foreach ($productDetail['transfer_items'] as $ti) {

                        if ( ! isset($ti['transfer_suppliers']) || empty($ti['transfer_suppliers'])) {
                            continue;
                        }

                        foreach ($ti['transfer_suppliers'] as $tsp) {
                            foreach ($tsp['transfer_seasons'] as $ts) {
                                if (false === $this->_validRangeDate($ts['season_date_from'], $ts['season_date_to'], $today)) {
                                    continue;
                                }
                                $tourSuppliers[] = true;
                            }
                        }
                    }
                }

                if (empty($tourSuppliers)) {
                    throw new \Exception('', ErrorConst::ERROR_RDB_NO_DATA_EXIST);
                }

                $this->_setRedis($redisKey, $productDetail);

                $productOld = RedisUtil::getData($this->redis, TFConst::REDIS_TF_PRODUCTS_REDIS_KEY, $this->redisSession);
                $product = [
                    'productKey' => (int)$productIdx,
                    'updatedAt'  => $productDetail['modify_datetime'] ?? date('Y-m-d H:i:s'),
                    'title'      => $this->_replaceSpecialChar($productDetail['product_ko'])
                ];

                if (false !== ($seq = array_search($productIdx, array_column($productOld, 'productKey')))) {
                    unset($productOld[$seq]);
                }
                array_push($productOld, $product);

                $products = [];
                foreach ($productOld as $row) {
                    $products[] = [
                        'productKey' => (int)$row['productKey'],
                        'updatedAt'  => $row['updatedAt'],
                        'title'      => $row['title'],
                    ];
                }

                $this->_setRedis(TFConst::REDIS_TF_PRODUCTS_REDIS_KEY, $products);

                // 웹훅 호출
                if (APP_ENV == APP_ENV_PRODUCTION || APP_ENV === APP_ENV_STAGING) {
                    RedisUtil::flush($this->redis, CommonConst::REDIS_BIZ_RES_SESSION);
                    $this->_passthru([
                        'productKey' => $productIdx,
                        'updateType' => 'UPDATE',
                        'updateAt'   => $productDetail['modify_datetime'],
                    ]);
                }

                $results = [
                    'result' => true,
                    'data'   => $productDetail
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

            $totalAmount = 0;

            try {

                $params = $request->getAttribute('params');

                if (false === CommonUtil::validateParams($params, ['product', 'adults', 'children', 'teenagers', 'arrivalDate'], true)) {
                    throw new \Exception(null, ErrorConst::ERROR_NOT_FOUND_REQUIRE_FIELD);
                }

                $product = $params['product'];

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
                    'adults'              => (int)($params['adults'] ?? 0),     // 성인
                    'seniors'             => (int)($params['seniors'] ?? 0),    // 청소년
                    'teenagers'           => (int)($params['teenagers'] ?? 0),  // 아동
                    'children'            => (int)($params['children'] ?? 0),   // 유아(가격있음)
                    'infants'             => (int)($params['infants'] ?? 0),    // 영유아
                ];

                if (!isset($params['options']['perPax'])) {
                    throw new \Exception('인원별 옵션이 지정되지 않았습니다.');
                }

                // 인원수와 명단 체크
                $participantCnt = $result['adults'] + $result['children'] + $result['teenagers'] + $result['seniors'] + $result['infants'];
                if ($participantCnt > count($params['options']['perPax'])) {
                    throw new \Exception('예약 생성에 실패하였습니다 - 인원 상이');
                }

                $arrivalDate = date('Ymd', strtotime($params['arrivalDate']));

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

                                $availAbleBooking = false;
                                foreach ($ts['tour_seasons'] as $tss) {

                                    // 날짜 비교
                                    if (false === $this->_validRangeDate($tss['season_date_from'], $tss['season_date_to'], $arrivalDate)) {
                                        continue;
                                    }

                                    $weeks = explode( ',', $tss['season_week']);
                                    $tmpWeek = date('w', strtotime($arrivalDate));

                                    if (! in_array($tmpWeek, $weeks)) {
                                        continue;
                                    }

                                    $result['tour_season_idx'] = (int)$tss['tour_season_idx'];

                                    // 성인 가격 IDX
                                    if ( ! empty($params['adults'])) {
                                        $adultsCount = $params['adults'];
                                        do {
                                            $adultsCount--;
                                            foreach ($tss['tour_prices'] as $tp) {
                                                if ($tp['sale_unit_ko'] === TFConst::TF_ADULT_LABEL) {
                                                    $tourCustomIdxs[] = (int)$tp['tour_customer_idx'];
                                                }
                                            }
                                        } while (0 < $adultsCount);
                                    }

                                    // 청소년 가격 IDX
                                    if ( ! empty($params['seniors'])) {
                                        $seniorsCount = $params['seniors'];
                                        do {
                                            $seniorsCount--;
                                            foreach ($tss['tour_prices'] as $tp) {
                                                if ($tp['sale_unit_ko'] === TFConst::TF_TEENAGE_LABEL) {
                                                    $tourCustomIdxs[] = (int)$tp['tour_customer_idx'];
                                                }
                                            }
                                        } while (0 < $seniorsCount);
                                    }

                                    // 아동 가격 IDX
                                    if ( ! empty($params['teenagers'])) {
                                        $childrenCount = $params['teenagers'];
                                        do {
                                            $childrenCount--;
                                            foreach ($tss['tour_prices'] as $tp) {
                                                if ($tp['sale_unit_ko'] === TFConst::TF_CHILD_LABEL) {
                                                    $tourCustomIdxs[] = (int)$tp['tour_customer_idx'];
                                                }
                                            }
                                        } while (0 < $childrenCount);
                                    }

                                    // 유아가격 IDX (가격있음)
                                    if ( ! empty($params['children'])) {
                                        $infantCount = $params['children'];
                                        do {
                                            $infantCount--;
                                            foreach ($tss['tour_prices'] as $tp) {
                                                if ($tp['sale_unit_ko'] === TFConst::TF_INFANT_LABEL) {
                                                    $tourCustomIdxs[] = (int)$tp['tour_customer_idx'];
                                                }
                                            }
                                        } while (0 < $infantCount);
                                    }

                                    // 영유아가격 IDX (가격없음)
                                    if ( ! empty($params['infants'])) {
                                        $infantCount = $params['infants'];
                                        do {
                                            $infantCount--;
                                            foreach ($tss['tour_prices'] as $tp) {
                                                if ($tp['sale_unit_ko'] === TFConst::TS_INFANT_LABEL) {
                                                    $tourCustomIdxs[] = (int)$tp['tour_customer_idx'];
                                                }
                                            }
                                        } while (0 < $infantCount);
                                    }

                                    $availAbleBooking = true;
                                }

                                if ($availAbleBooking === false) {
                                    throw new \Exception('예약 생성에 실패하였습니다. - 예약 날짜 오류 (' . $arrivalDate . ' / ' . json_encode($result, JSON_UNESCAPED_UNICODE));
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

                        if (!isset($params['options']['perBooking'])) {
                            throw new \Exception('주문별 옵션이 지정되지 않았습니다.');
                        }

                        $perBookingOption = $params['options']['perBooking'] ?? null;
                        $transferInfo = array_shift($perBookingOption);

                        if ( ! isset($transferInfo['id']) || empty($transferInfo['id'])) {
                            throw new \Exception('예약 생성에 실패하였습니다. (픽업상품의 ID가 없습니다)');
                        }

                        if ( $transferInfo['id'] !== 'transfer_price_idx' ) {
                            throw new \Exception('예약 생성에 실패하였습니다. (픽업상품의 ID가 없습니다)');
                        }

                        if ( ! isset($transferInfo['value']) || empty($transferInfo['value'])) {
                            throw new \Exception('예약 생성에 실패하였습니다. (픽업상품의 ID가 없습니다)');
                        }

                        $transferPriceIdx = $transferInfo['value'];

                        foreach ($tourSuppliers as $ts) {
                            if (isset($ts['transfer_seasons']) && is_array($ts['transfer_seasons'])) {

                                $availAbleBooking = false;
                                foreach ($ts['transfer_seasons'] as $tss) {

                                    if (false === (array_search($transferPriceIdx, array_column($tss['transfer_prices'], 'transfer_price_idx')))) {
                                        continue;
                                    }

                                    // 날짜 비교
                                    if (false === $this->_validRangeDate($tss['season_date_from'], $tss['season_date_to'], $arrivalDate)) {
                                        continue;
                                    }

                                    $weeks = explode( ',', $tss['season_week']);
                                    $tmpWeek = date('w', strtotime($arrivalDate));

                                    if (! in_array($tmpWeek, $weeks)) {
                                        continue;
                                    }

                                    $result['transfer_price_idx'] = (int)$transferPriceIdx;
                                    $result['options']['perBooking'] = $perBookingOption;

                                    $availAbleBooking = true;

                                }

                                if ($availAbleBooking === false) {
                                    throw new \Exception('예약 생성에 실패하였습니다. - 예약 날짜 오류 (' . $arrivalDate . ' / ' . json_encode($result, JSON_UNESCAPED_UNICODE));
                                }

                            }
                        }

                        break;
                }

                $redisArr = ['action' => 'addBooking', 'datetime' => date('Y-m-d H:i:s')];
                $redisArr = array_merge($redisArr, $result);

                $eventKey = $this->_getRedisEventKey($redisArr);
                $this->guzzleOpt['form_params'] = ['event_key' => $eventKey];
                $this->guzzleOpt['allow_redirects'] = true;
                $targetUrl = TFConst::TF_BOOK_ADD_URL;
                $addResult = Http::httpRequest($targetUrl, $this->guzzleOpt, CommonConst::REQ_METHOD_POST);

                if (empty($addResult)) {
                    throw new \Exception('예약 생성에 실패하였습니다. - TF 통신 오류');
                }

                if (!empty($addResult['errors'])) {
                    throw new \Exception('예약 생성에 실패하였습니다. TF - 에러 :: ' . json_encode($addResult['errors']));
                }

                if (!isset($addResult['book']['book_cd']) || empty($addResult['book']['book_cd'])) {
                    throw new \Exception('예약 생성에 실패하였습니다. - TF 부킹번호 부재');
                }

                $bookIdx = $addResult['book']['book_cd'];
                switch ($addResult['book']['book_status_cd']) {
                    case TFConst::TF_BOOK_CD_INS :
                    case TFConst::TF_BOOK_CD_ORD :
                    case TFConst::TF_BOOK_CD_REQ :
                        $status = TFConst::RESERVE_STATUS_RESV;
                        break;
                    default :
                        throw new \Exception('예약 생성에 실패하였습니다. - 진행할 수 있는 예약이 아닙니다. (' . $addResult['book']['book_status_cd'] . ')');
                }

                $totalAmount = $addResult['book']['sale_price'] ?? 0;
                if (empty($totalAmount)) {
                    throw new \Exception('예약 생성에 실패하였습니다. - 예약금액 0');
                }

                $results = [
                    'result' => [
                        'success'          => true,
                        'status'           => $status,
                        'bookingKey'       => $bookIdx,
                        'partnerReference' => $params['partnerReference'] ?? null,
                        'totalAmount'      => $totalAmount,
                    ]
                ];

                $saveData = $results['result'];
                $saveData['tf_result'] = $addResult;

                $this->_setBookingRedis($bookIdx, $saveData);
                $this->_saveBookingLog($bookIdx, $saveData);

            } catch (\Exception $ex) {

                $results = [
                    'result' => [
                        'success'     => false,
                        'status'      => TFConst::STATUS_ERROR,
                        'bookingKey'  => null,
                        'totalAmount' => $totalAmount,
                        'message'     => $this->_getErrorMessage($ex, true, [
                            'subject' => '부킹 생성에러',
                            'body'    => $ex->getMessage(),
                        ]),
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
                    throw new \Exception('이미 예약 진행중 이거나 진행 가능한 주문이 아닙니다.');
                }

                $this->guzzleOpt['json'] = ['event_key' => $this->_getRedisEventKey([
                    'action' => 'proceedBooking',
                    'bookingKey' => $args['bookingKey'],
                    'datetime'   => date('Y-m-d H:i:s'),
                ])];

                $this->guzzleOpt['allow_redirects'] = true;
                $targetUrl = TFConst::TF_BOOK_PROC_URL . '/' . $args['bookingKey'];
                $procResult = Http::httpRequest($targetUrl, $this->guzzleOpt, CommonConst::REQ_METHOD_PATCH);

                if (empty($procResult)) {
                    throw new \Exception('예약 진행에 실패하였습니다. - TF 서버오류');
                }

                if (!empty($procResult['errors'])) {
                    throw new \Exception('예약 진행에 실패하였습니다. TF - 에러 :: ' . json_encode($procResult['errors']));
                }

                if (!isset($procResult['book']['book_cd']) || empty($procResult['book']['book_cd'])) {
                    throw new \Exception('예약 진행에 실패하였습니다. TF - 에러 :: 부킹번호 부재');
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

                $this->_setBookingRedis($results['bookingKey'], $results);
                $this->_saveBookingLog($results['bookingKey'], $results, ($procResult['book']['book_datetime'] ?? null));

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

                $body = $ex->getMessage();
                $body .= ' ' . json_encode($results, JSON_UNESCAPED_UNICODE) . PHP_EOL;
                $this->_getErrorMessage($ex, true, ['subject' => '부킹 진행에러', 'body' => $body]);

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

                if ($bookingStatusRedis['status'] === TFConst::CANCEL_STATUS_DENY) {
                    throw new \Exception('예약 취소 가능한 주문이 아닙니다.');
                }

                //if ($bookingStatusRedis['status'] === TFConst::RESERVE_STATUS_CNCL || $bookingStatusRedis['status'] === TFConst::CANCEL_STATUS_WAIT) {
                //    throw new \Exception('예약 취소 진행중 입니다.');
                //}

                $subject = 'TF 예약취소 요청';
                $body = '<h1>TF 예약취소 요청</h1>'
                    . '<p>주문 키 : ' . $bookingStatusRedis['bookingKey'] . '</p>'
                    . '<p>주문금액 : ' . number_format($bookingStatusRedis['totalAmount']) . '</p>'
                    . '<p>환불금액 : ' . number_format($bookingStatusRedis['refundAmount']) . '</p>'
                ;

                // 주문취소는 TF에 메일로 발송
                if (false === AwsUtil::sendEmail($this->recipients, $subject, $body)) {
                    throw new \Exception(null, ErrorConst::ERROR_SEND_EMAIL);
                }

                $results = [
                    'success'          => true,
                    'status'           => TFConst::CANCEL_STATUS_WAIT,
                    'bookingKey'       => $args['bookingKey'],
                    'partnerReference' => $bookingStatusRedis['partnerReference'] ?? null,
                    'totalAmount'      => $bookingStatusRedis['totalAmount'],
                    'refundAmount'     => $bookingStatusRedis['refundAmount'],
                    'tf_result'        => $bookingStatusRedis['tf_result']
                ];

                $this->_setBookingRedis($args['bookingKey'], $results);
                $this->_saveBookingLog($args['bookingKey'], $results, ($bookingStatusRedis['tf_result']['book']['book_datetime'] ?? null));

                unset($results['tf_result']);
                unset($results['totalAmount']);
                unset($results['partnerReference']);

            } catch (\Exception $ex) {

                $results = [
                    'success'      => false,
                    'status'       => TFConst::STATUS_ERROR,
                    'bookingKey'   => $args['bookingKey'],
                    'refundAmount' => 0,
                    'message'      => $ex->getMessage(),
                ];

                $body = $ex->getMessage();
                $body .= ' ' . json_encode($results, JSON_UNESCAPED_UNICODE) . PHP_EOL;
                $this->_getErrorMessage($ex, true, ['subject' => '부킹 취소에러', 'body' => $body]);

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

                $bookingDataType = 'redis';
                if (false === ($bookingData = RedisUtil::getData($this->redis, $redisKey, $redisDb))) {

                    $this->guzzleOpt['query'] = [
                        'event_key' => $this->_getRedisEventKey([
                            'action' => 'checkBookingStatus',
                            'bookingKey' => $args['bookingKey'],
                            'datetime'   => date('Y-m-d H:i:s'),
                        ])
                    ];

                    $this->guzzleOpt['allow_redirects'] = true;
                    $targetUrl = TFConst::TF_BOOK_PROC_URL . '/' . $args['bookingKey'];
                    $bookingData = Http::httpRequest($targetUrl, $this->guzzleOpt);
                    $bookingDataType = 'travel';

                    if (!isset($bookingData['book']) || empty($bookingData['book'])) {
                        throw new \Exception('예약번호가 존재하지 않습니다. ('. $args['bookingKey'] .')');
                    }

                    if (!empty($bookingData['errors'])) {
                        throw new \Exception('예약 조회에 실패하였습니다. - TF Error [errors] ('. $args['bookingKey'] .')');
                    }

                }

                $redisStatus = $bookingData['status'] ?? null;
                $tfResult = (isset($bookingData['tf_result'])) ? $bookingData['tf_result'] : $bookingData;
                $bookingStatus = (isset($bookingData['tf_result'])) ? $bookingData['tf_result']['book'] : $bookingData['book'];

                if (!isset($bookingStatus['book_status_cd']) || empty($bookingStatus['book_status_cd'])) {
                    throw new \Exception('예약번호가 존재하지 않습니다. ('. $args['bookingKey'] .')');
                }

                $results = [
                    'success'          => true,
                    'status'           => $redisStatus ?? $this->_getStatusCode($bookingStatus['book_status_cd']),
                    'bookingKey'       => $bookingStatus['book_cd'],
                    'partnerReference' => $bookingData['partnerReference'] ?? null,
                    'totalAmount'      => $bookingStatus['sale_price'] ?? 0,
                    'refundAmount'     => $bookingStatus['sale_price'] ?? 0,
                    'tf_result'        => $tfResult
                ];

                if ($results['status'] === TFConst::RESERVE_STATUS_CFRM) {
                    $results['voucher'][] = [
                        'contextType' => 'text/html',
                        'url'         => 'https://www.travelforest.co.kr/voucher/' . $bookingStatus['book_cd'],
                        'urlType'     => 'LINK'
                    ];
                }

                $this->_setBookingRedis($bookingStatus['book_cd'], $results);
                unset($results['tf_result']);

            } catch (\Exception $ex) {

                $results = [
                    'success'      => false,
                    'status'       => TFConst::STATUS_ERROR,
                    'bookingKey'   => $args['bookingKey'],
                    'message'     => $this->_getErrorMessage($ex, true, [
                        'subject' => '예약 확인 에러',
                        'body'    => $ex->getMessage(),
                    ]),
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
            $updateType = null;
            $oldStatus = null;

            try {

                if (false === CommonUtil::validateParams($args, ['bookingKey', 'updateType'], true)) {
                    throw new \Exception(null, ErrorConst::ERROR_NOT_FOUND_REQUIRE_FIELD);
                }

                // confirm || cancel || close
                switch ($args['updateType']) {
                    case TFConst::TF_BOOK_CD_CFM :
                    case TFConst::TF_BOOK_CD_CCL :
                    case TFConst::TF_BOOK_CD_CLS :
                        break;
                    default :
                        throw new \Exception(null, ErrorConst::ERROR_STEP_FAIL);
                }

                $redisDb = CommonConst::REDIS_MESSAGE_SESSION;
                $redisKey = TFConst::REDIS_TF_BOOKING_REDIS_KEY . '_' . $args['bookingKey'];

                if (false === ($bookingStatusRedis = RedisUtil::getData($this->redis, $redisKey, $redisDb))) {
                    throw new \Exception(ErrorConst::ERROR_DESCRIPTION_ARRAY[ErrorConst::ERROR_RDB_NO_DATA_EXIST]);
                }

                $oldStatus = $bookingStatusRedis['status'];
                $updateType = $this->_getStatusCode($args['updateType']);

                if (empty($updateType)) {
                    throw new \Exception(null, ErrorConst::ERROR_STEP_FAIL);
                }

                $this->guzzleOpt['query'] = ['event_key' => $this->_getRedisEventKey([
                    'action'     => 'bookingUpdate',
                    'updateType' => $args['updateType'],
                    'bookingKey' => $args['bookingKey'],
                    'datetime'   => date('Y-m-d H:i:s'),
                ])];

                $this->guzzleOpt['allow_redirects'] = true;
                $statusTargetUrl = TFConst::TF_BOOK_PROC_URL . '/' . $args['bookingKey'];
                $bookingStatus = Http::httpRequest($statusTargetUrl, $this->guzzleOpt, CommonConst::REQ_METHOD_GET);
                $curStatus = $bookingStatus['book']['book_status_cd'];

                LogMessage::debug('Req updateType  :: ' . $args['updateType']);
                LogMessage::debug('Trevel cur book_status_cd :: ' . $curStatus);

                if ($args['updateType'] != $curStatus) {
                    throw new \Exception(null, ErrorConst::ERROR_STEP_FAIL);
                }

                // 미리 CONFIRM 상태로 업데이트
                $bookingStatusRedis['status'] = $updateType;
                $this->_setBookingRedis($args['bookingKey'], $bookingStatusRedis);

                // Webhook
                if (APP_ENV == APP_ENV_PRODUCTION || APP_ENV === APP_ENV_STAGING) {

                    $targetUrl = (APP_ENV === APP_ENV_PRODUCTION) ? TFConst::TS_WEBHOOK_URL : TFConst::TS_WEBHOOK_DEV_URL ;
                    $targetUrl .= TFConst::TS_RESERVE_WEBHOOK_URI;

                    $sleep = (APP_ENV === APP_ENV_PRODUCTION) ? 300 : 3;
                    $maxTryCount = (APP_ENV === APP_ENV_PRODUCTION) ? $this->maxTryCount : 3;
                    $tryCount = 0;

                    $updateParams = [
                        'bookingKey' => $args['bookingKey'],
                        'updateType' => $updateType,
                        'updateAt'   => date('Y-m-d H:i:s')
                    ];

                    //if ($updateType === TFConst::TF_BOOK_CD_CFM) {
                    //    $updateParams['voucher'] = [
                    //        'contextType' => 'text/html',
                    //        'url'         => 'https://www.travelforest.co.kr/voucher/' . $args['bookingKey'],
                    //        'urlType'     => 'LINK'
                    //    ];
                    //}

                    do {
                        $result = $this->_webhook($targetUrl, $updateParams, $this->noWait, $sleep);
                        $tryCount++;
                    } while ($result['success'] === false && $tryCount <= $maxTryCount);

                    if ( ! isset($result['success']) || true !== $result['success']) {
                        $bookingStatusRedis['status'] = $oldStatus;
                        $this->_setBookingRedis($args['bookingKey'], $bookingStatusRedis);
                        throw new \Exception('Webhook Error');
                    }
                }

                $results = [
                    'success'          => true,
                    'status'           => $updateType,
                    'bookingKey'       => $bookingStatus['book']['book_cd'],
                    'partnerReference' => $bookingStatusRedis['partnerReference'] ?? null,
                    'totalAmount'      => $bookingStatus['book']['sale_price'],
                    'refundAmount'     => $bookingStatus['book']['sale_price'],
                    'tf_result'        => $bookingStatus,
                ];

                RedisUtil::setDataWithExpire($this->redis, $redisDb, $redisKey, CommonConst::REDIS_SESSION_EXPIRE_TIME_DAY_7, $results);
                $this->_saveBookingLog($args['bookingKey'], $results, ($bookingStatus['book']['book_datetime'] ?? null));

                unset($results['tf_result']);

                $body = '<h1>주문정보 업데이트 처리요청</h1>' . '<p>주문 키 : ' . $args['bookingKey'] . '</p>' . '<p>' . $args['updateType'] . ' 처리 성공</p>';

            } catch (\Exception $ex) {

                $body = '<h1>주문정보 업데이트 처리요청</h1>' . '<p>주문 키 : ' . $args['bookingKey'] . '</p>' . '<p>' . $args['updateType'] . ' 처리 실패</p>';

                $results = [
                    'success'      => false,
                    'status'       => TFConst::STATUS_ERROR,
                    'bookingKey'   => $args['bookingKey'],
                    'message'      => $this->_getErrorMessage($ex, true, ['subject' => '', 'body' => $body]),
                ];
            }

            if (APP_ENV === APP_ENV_PRODUCTION || APP_ENV === APP_ENV_STAGING) {
                try {
                    if (false === AwsUtil::sendEmail($this->recipients, '주문정보 업데이트 결과', $body)) {
                        throw new \Exception(null, ErrorConst::ERROR_SEND_EMAIL);
                    }
                } catch (\Exception $ex) {

                }
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

                $targetUrl = (APP_ENV === APP_ENV_PRODUCTION) ? TFConst::TS_WEBHOOK_URL : TFConst::TS_WEBHOOK_DEV_URL ;
                $targetUrl .= TFConst::TS_PRODUCT_WEBHOOK_URI;

                $parameters = $request->getAttribute('params');
                switch (true) {
                    case (is_array($parameters) && is_array($args)) :
                        $params = array_merge($parameters, $args);
                        break;
                    case (is_array($parameters) && !empty($parameters)) :
                        $params = $parameters;
                        break;
                    default :
                        $params = $args;
                        break;
                }

                if (false === CommonUtil::validateParams($params, ['productKey', 'updateType'], true)) {
                    throw new \Exception(null, ErrorConst::ERROR_NOT_FOUND_REQUIRE_FIELD);
                }

                switch ($params['updateType']) {
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

                if ($updateType === 'ADD' || $updateType === 'UPDATE') {
                    if (false === CommonUtil::validateParams($params, ['title'], true)) {
                        throw new \Exception(null, ErrorConst::ERROR_NOT_FOUND_REQUIRE_FIELD);
                    }
                }

                if (false === ($products = RedisUtil::getData($this->redis, TFConst::REDIS_TF_PRODUCTS_REDIS_KEY, $this->redisSession))) {
                    throw new \Exception('', ErrorConst::ERROR_RDB_NO_DATA_EXIST);
                }

                // 상품검색
                if (false === ($productKeyIdx = array_search($params['productKey'], array_column($products, 'productKey')))) {
                    if ($updateType !== 'ADD') {
                        throw new \Exception('', ErrorConst::ERROR_RDB_NO_DATA_EXIST);
                    }
                }

                $updateParams = [
                    'productKey'     => $params['productKey'],
                    'productTypeKey' => $params['productTypeKey'] ?? null,
                    'updateType'     => $updateType,
                    'updateAt'       => date('Y-m-d H:i:s')
                ];

                // Redis 갱신
                switch ($params['updateType']) {
                    case 1 : // 상품추가
                        $products[] = [
                            'productKey' => $params['productKey'],
                            'title'      => '',
                            'updateAt'   => date('Y-m-d H:i:s')
                        ];
                        break;
                    case 2 : // 상품삭제
                        unset($products[$productKeyIdx]);
                        break;
                    case 3 : // 업데이트
                        $products[$productKeyIdx] = [
                            'productKey' => $updateParams['productKey'],
                            'updateAt'   => $updateParams['updateAt'],
                            'title'      => $params['title'] ?? ($products[$productKeyIdx]['title'] ?? ''),
                        ];
                        break;
                }

                $this->_setRedis(TFConst::REDIS_TF_PRODUCTS_REDIS_KEY, $products);

                // webhook
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


        public function getEventKey(Request $request, Response $response)
        {
            $results = $this->jsonResult;

            try {

                $results['data']['event_key'] = $this->_getRedisEventKey([], CommonConst::REDIS_SESSION_EXPIRE_TIME_MIN_10);

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
         * @param Request  $request
         * @param Response $response
         *
         * @return Response
         */
        public function flushRedis(Request $request, Response $response)
        {
            try {

                $params = $request->getAttribute('params');

                if (false === CommonUtil::validateParams($params, ['db'], true)) {
                    throw new \Exception(null, ErrorConst::ERROR_NOT_FOUND_REQUIRE_FIELD);
                }

                RedisUtil::flush($this->redis, $params['db'], ($params['key'] ?? null), ($params['mode'] ?? null));

                $results = [
                    'result' => true,
                    'data'   => $params,
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
         *
         * @param Request  $request
         * @param Response $response
         *
         * @return Response
         */
        public function updateTideProduct(Request $request, Response $response)
        {
            try {

                $params = $request->getAttribute('params');

                if (empty($params) || ! is_array($params)) {
                    throw new \Exception(null, ErrorConst::ERROR_NOT_FOUND_REQUIRE_FIELD);
                }

                if (false === ($oldProducts = RedisUtil::getData($this->redis, TFConst::REDIS_TF_PRODUCTS_REDIS_KEY, $this->redisSession))) {
                    throw new \Exception('', ErrorConst::ERROR_RDB_NO_DATA_EXIST);
                }

                $oldProductKeys = array_column($oldProducts, 'productKey');

                // 비즈의 Response Redis Data 삭제
                RedisUtil::flush($this->redis, CommonConst::REDIS_BIZ_RES_SESSION);

                $results = [
                    'result' => true,
                    'data'   => $params,
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

                $webHookOption = [
                    'verify'          => false,
                    'timeout'         => CommonConst::HTTP_RESPONSE_TIMEOUT,
                    'connect_timeout' => CommonConst::HTTP_CONNECT_TIMEOUT,
                    'headers'         => [
                        'User-Agent'    => 'Synctree/2.1 - ' . APP_ENV,
                        'Access-Token'  => (APP_ENV === APP_ENV_PRODUCTION) ? TFConst::TS_ACCESS_TOKEN : TFConst::TS_DEV_ACCESS_TOKEN,
                        'Supplier-Code' => TFConst::TS_SUPPLIER_CODE,
                        'Content-Type'  => 'application/json',
                        'Accept'        => 'application/json'
                    ],
                    'json' => $params,
                    'allow_redirects' => true,
                ];

                $result = Http::httpRequest($targetUrl, $webHookOption, CommonConst::REQ_METHOD_PUT);

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
        private function _validRangeDate($from, $to, $arrivalDate = '')
        {
            $tourDateFrom = date('Y-m-d', strtotime($from));
            $tourDateTo = date('Y-m-d', strtotime($to));

            if (empty($arrivalDate)) {
                $arrivalDate = date('Y-m-d');
            } else {
                $arrivalDate = date('Y-m-d', strtotime($arrivalDate));
            }

            if ($arrivalDate < $tourDateFrom || $arrivalDate > $tourDateTo) {
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

        private function _setBookingRedis($bookKey, $data, $expire = CommonConst::REDIS_SESSION_EXPIRE_TIME_DAY_90)
        {
            $result = true;

            $redisDb = CommonConst::REDIS_MESSAGE_SESSION;
            $redisKey = TFConst::REDIS_TF_BOOKING_REDIS_KEY . '_' . $bookKey;

            try {
                $result = RedisUtil::setDataWithExpire($this->redis, $redisDb, $redisKey, $expire, $data);
            } catch (\Exception $ex) {
                LogMessage::error('TravelForest - Set Redis Error (' . $redisKey . ')');
            }

            return $result;

        }

        private function _saveBookingLog($bookingIdx, $contents = [], $timestamp = null)
        {

            $result = true;

            try {

                $curDateTime = $timestamp ?? date('Y-m-d H:i:s');
                $contents['timestamp'] = $curDateTime;
                $contents = json_encode($contents, JSON_UNESCAPED_UNICODE);

                $filePath = BASE_DIR . '/logs/biz/';
                $fileName = 'TFbooking.' . $bookingIdx . '.' . APP_ENV . '.log';
                $file = $filePath . $fileName;

                $logfile = fopen($file, 'a');
                fwrite($logfile, $contents . "\n");
                fclose($logfile);

                $s3FileName = date('Y/m/d', strtotime($curDateTime));
                $s3FileName .= '/booking/' . $fileName;

                AwsUtil::s3FileUpload($s3FileName, $file, 's3Log', 'tidesquare');

            } catch (\Exception $ex) {
                $result = false;
            }

            return $result;
        }

        /**
         * @param $str
         *
         * @return mixed
         */
        private function _replaceSpecialChar($str)
        {
            // HTML entities 변환
            $retStr = htmlspecialchars_decode($str);

            // 구글맵 Class 추가
            $retStr = preg_replace('/(<iframe)\s?[^>]*(src\s*=\s*[\'"])(https:\/\/www.google.com\/maps)/', '$1 class="supplier_gmaps" $2$3', $retStr);

            return $retStr;

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

        /**
         * @param $data
         */
        private function _passthru($data)
        {
            // 개발/운영 웹훅 모두 호출로 변경
            $webHookArray = [TFConst::TS_WEBHOOK_URL, TFConst::TS_WEBHOOK_DEV_URL];
            foreach ($webHookArray as $url) {
                //$targetUrl = (APP_ENV === APP_ENV_PRODUCTION) ? TFConst::TS_WEBHOOK_URL : TFConst::TS_WEBHOOK_DEV_URL ;
                //$targetUrl .= TFConst::TS_PRODUCT_WEBHOOK_URI;
                $targetUrl = $url;
                $targetUrl .= TFConst::TS_PRODUCT_WEBHOOK_URI;
                $command = 'curl';
                $command .= ' -H "Content-type: application/json"';
                $command .= ' -H "Accept: application/json"';
                $command .= ' -H "Access-Token: '. (($url === TFConst::TS_WEBHOOK_URL) ? TFConst::TS_ACCESS_TOKEN : TFConst::TS_DEV_ACCESS_TOKEN) .'"';
                $command .= ' -H "Supplier-Code: '. TFConst::TS_SUPPLIER_CODE .'"';
                $command .= ' -d \'' . json_encode($data, JSON_UNESCAPED_UNICODE) . '\'';
                $command .= ' -X PUT ' . $targetUrl . ' -s > /dev/null 2>&1 &';
                LogMessage::debug('TS Webhook curl command :: ' . $command);
                passthru($command);
                sleep(1);
            }
        }


    }