<?php

namespace libraries\util;

use Spatie\ArrayToXml\ArrayToXml;
use libraries\{
    constant\SynctreeConst,
    util\CommonUtil,
    log\LogMessage
};

class XmlUtil
{
    private static $config;

    /**
     * 티웨이 요청용 XML 생성
     *
     * @param array  $params
     * @param string $service
     *
     * @return string
     */
    public static function getRequestXML($params = [], $service = 'airAvailability')
    {

        self::$config = include APP_DIR . 'config/' . APP_ENV . '.php';
        $apiConfig = self::$config['settings']['api'] ?? null;

        if (empty($params)) {
            return false;
        }

        $requestArray = [];
        $agencyCode = $apiConfig['tway_api']['agency_code'];

        switch ($service) {

            case 'airAvailability' :
                $dataArray['AirlineCode'] = SynctreeConst::TWAY_XML_DEFAULT_AIRLINE_CODE;
                $dataArray['AvailabilitySearches'] = [
                    [
                        'Origin' => $params['depart_air_code'],
                        'Destination' => $params['arrival_air_code'],
                        'TravelDate' => $params['depart_date'],
                    ],
                    [
                        'Origin' => $params['arrival_air_code'],
                        'Destination' => $params['depart_air_code'],
                        'TravelDate' => $params['return_date'],
                    ]
                ];

                foreach ($params['pax'] as $paxes) {
                    $dataArray['PaxCountDetails'][] = [
                        'PaxType' => $paxes['pax_type'],
                        'PaxCount' => $paxes['pax_count'],
                    ];
                }

                $dataArray['FareLevels'] = SynctreeConst::TWAY_XML_DEFAULT_FARE_LEVEL;
                $dataArray['TripType'] = $params['trip_type'];
                break;

            default :
                $dataArray = [];
        }

        $dataArray['AgencyCode'] = $agencyCode;
        $requestArray[SynctreeConst::TWAY_XML_AIRAVAILABILITY_RQ] = $dataArray;

        return ArrayToXml::convert($requestArray, SynctreeConst::TWAY_XML_AIRAVAILABILITY_RQMSG);
    }

    /**
     * XML -> array 변환
     * @param $xmlData
     *
     * @return bool|array
     */
    public static function xmlToArray($xmlData)
    {
        if (!empty($xmlData)) {

            $xml = simplexml_load_string($xmlData);
            //$result = [
            //    'token_id' => $xml->attributes()->TokenID,
            //    'origin_data' => $xml,
            //];

//            $ow = $xml->AirAvailabilityRS->OriginDestinationInfo->attributes();
//            foreach ($ow as $key => $val) {
//                echo '<p>' . $key . ' :: ' . $val . '</p>';
//            }

            return $xml;

        }

        return false;
    }


}