<?php
namespace libraries\constant;

/**
 * Class TSConst
 *
 * 타이드스퀘어 정의 상수
 *
 * @package libraries\constant
 */
class TSConst
{
    // ACCESS_TOKEN 은 업체마다 정의
    //const TS_ACCESS_TOKEN = 'FA12624437284939AAFEEE43625BFB55AD1E7476A9D01647DEA64DF7B3E5B1AB7DC16C55113251D7E9CCFA42FF15FB4F2E0A7781E2858BB6301D81C930CA3D48';
    //const TS_DEV_ACCESS_TOKEN = 'FA12624437284939AAFEEE43625BFB55AD1E7476A9D01647DEA64DF7B3E5B1AB7DC16C55113251D7E9CCFA42FF15FB4F2E0A7781E2858BB6301D81C930CA3D48';

    const TS_WEBHOOK_URL = 'https://tnaapi.tourvis.com'; // Webhook
    const TS_WEBHOOK_DEV_URL = 'https://devtnaadmin.tourvis.com'; // Webhook

    const TS_RESERVE_WEBHOOK_URI = '/apiary/common/webhook/v1/booking'; // 부킹상태 업데이트
    const TS_PRODUCT_WEBHOOK_URI = '/common/webhook/v1/product'; // 상품정보 업데이트

    const RESERVE_STATUS_RESV = 'RESERVED'; // 예약접수, 고객이 상품에 대한 주문을 한 상태입니다.
    const RESERVE_STATUS_CFRM = 'CONFIRM';  // 예약확정, 주문 상품의 예약이 확정된 상태입니다.
    const RESERVE_STATUS_WAIT = 'WAITING';  // 확정대기
    const RESERVE_STATUS_CNCL = 'CANCEL';   // 예약취소
    const CANCEL_STATUS_CFRM = self::RESERVE_STATUS_CNCL;
    const CANCEL_STATUS_WAIT =  'CANCEL_WAIT'; // 취소 대기
    const CANCEL_STATUS_DENY = 'CANCEL_DECLINED'; // 취소 거부
    const STATUS_ERROR = 'ERROR'; // 에러

    /** Errors */
    const ERROR_BOOKING_ADD = '예약 생성에 실패하였습니다.';
    const ERROR_BOOKING_ALEADY_PROC = '이미 예약 진행중 이거나 진행 가능한 주문이 아닙니다.';
    const ERROR_BOOKING_PROC = '예약 진행에 실패하였습니다.';





}