<?php
/**
 * Class KorbitAPI
 *
 * @url     https://www.korbit.co.kr/
 * @url     https://apidocs.korbit.co.kr/ko/
 * @url     https://api.korbit.co.kr/
 *
 * @author  Steve Park
 * @date    2017-11-27
 */

class KorbitAPI {
    protected $api_url = 'https://api.korbit.co.kr/';

    protected $api_key;
    protected $api_secret;

    private $access_token = array();

    public function __construct ($api_key, $api_secret) {
        $this->api_key = $api_key;
        $this->api_secret = $api_secret;

        if (isset($_SESSION['KorbitToken'])) {
            if ($_SESSION['KorbitToken']['expires_time'] <= time()) {
                unset($_SESSION['KorbitToken']);
            } else {
                $this->access_token = $_SESSION['KorbitToken'];
            }
        }
    }

    public function __destruct() {}

    /**
     * requestGET
     *
     * @param   string      $url
     * @param   array       $params
     *
     * @return  array       $response
     */
    private function requestGET ($url, $params = array()) {
        $request = array();

        // Set authorization bearer
        if (isset($this->access_token['token_type'])) {
            $authorization = 'Authorization: '.$this->access_token['token_type'].' '.$this->access_token['access_token'];
            $request[CURLOPT_HTTPHEADER] = array('Content-Type: application/json',$authorization);
        }

        $apiUrl = $this->api_url.$url;
        if (count($params)) {
            $apiUrl .= '?'.http_build_query($params);
        }

        $response = $this->request($apiUrl, $request);

        return $response;
    }

    /**
     * requestPOST
     *
     * @param   string      $url
     * @param   array       $params
     *
     * @return  array       $response
     */
    private function requestPOST ($url, $params = array()) {
        $request = array();
        $request[CURLOPT_POST] = TRUE;

        // Set authorization bearer
        if (isset($this->access_token['token_type'])) {
            $authorization = 'Authorization: '.$this->access_token['token_type'].' '.$this->access_token['access_token'];
            $request[CURLOPT_HTTPHEADER] = array('Content-Type: application/json',$authorization);
        }

        if (count($params)) {
            $request[CURLOPT_POSTFIELDS] = http_build_query($params);
        }

        $apiUrl = $this->api_url.$url;
        $response = $this->request($apiUrl, $request);

        return $response;
    }

    /**
     * request
     *
     * @param   string      $url
     * @param   array       $curlParms
     *
     * @return  array       $response
     */
    protected function request ($url, $curlParms = array()) {
        $response = array();

        // Set CURLOPT_USERAGENT
        if (!isset($curlParms[CURLOPT_USERAGENT])) {
            if (isset($_SERVER['HTTP_USER_AGENT'])) {
                $curlParms[CURLOPT_USERAGENT] = $_SERVER['HTTP_USER_AGENT'];
            } else {
                // Default IE11
                //$curlParms[CURLOPT_USERAGENT] = 'Mozilla/5.0 (Windows NT 10.0; WOW64; Trident/7.0; rv:11.0) like Gecko';
                $curlParms[CURLOPT_USERAGENT] = 'Mozilla/5.0';
            }
        }

        // Check curl_init
        if (function_exists('curl_init')) {
            $curl = curl_init();

            // Set URL
            curl_setopt($curl, CURLOPT_URL, $url);

            foreach ($curlParms as $api_key => $value) {
                curl_setopt($curl, $api_key, $value);
            }

            // Check SSL
            if (!isset($curlParms[CURLOPT_SSL_VERIFYPEER])) {
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
                curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
            }
            if (!isset($curlParms[CURLOPT_SSLVERSION])) {
                curl_setopt($curl, CURLOPT_SSLVERSION, 6);
            }

            // No header
            if (!isset($curlParms[CURLOPT_HEADER])) {
                curl_setopt($curl, CURLOPT_HEADER, FALSE);
            }

            // POST / GET (default : GET)
            if (!isset($curlParms[CURLOPT_POST]) && !isset($curlParms[CURLOPT_CUSTOMREQUEST])) {
                curl_setopt($curl, CURLOPT_POST, FALSE);
            }

            // Get response of php value
            if (!isset($curlParms[CURLOPT_RETURNTRANSFER])) {
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
            }

            curl_setopt($curl, CURLOPT_TIMEOUT, 10);

            /*
            // HTTP2
            if (!isset($curlParms[CURLOPT_HTTP_VERSION])) {
                curl_setopt($curl, CURLOPT_HTTP_VERSION, 3);
            }
            */
            if (!isset($curlParms[CURLINFO_HEADER_OUT])) {
                curl_setopt($curl, CURLINFO_HEADER_OUT, TRUE);
            }

            $curlResponse = curl_exec($curl);

            if (!$curlResponse)  {
                $response = curl_error($curl);
            } else {
                $resultDecode = json_decode($curlResponse, true);
                $response['json'] = $resultDecode;
                $response['response'] = curl_getinfo($curl);
            }

            curl_close($curl);
        }

        else {
            return false;
        }

        return $response;
    }

    /**
     * getTicker : Exchange Public API
     *
     * 최종 체결 가격
     *
     * @param   array       $param
     *          string      $param['currency_pair']     btc_krw (비트코인) / etc_krw (이더리움 클래식) / eth_krw (이더리움) /
     *                                                  xrp_krw (리플) / bch_krw (비트코인 캐시)
     *
     * @return  array       $result
     *          int         $result['timestamp']        최종 체결 시각 (Unix timestamp in msec)
     *          int         $result['last']             최종 체결 가격
     */
    public function getTicker ($param) {
        $result = array();

        $response = $this->requestGET('v1/ticker', $param);

        if (isset($response['response']['http_code']) && $response['response']['http_code'] == 200) {
            $result = $response['json'];
        } else {}

        return $result;
    }

    /**
     * getDetailedTicker : Exchange Public API
     *
     * 시장 현황 상세정보
     *
     * @param   array       $param
     *          string      $param['currency_pair']     btc_krw (비트코인) / etc_krw (이더리움 클래식) / eth_krw (이더리움) /
     *                                                  xrp_krw (리플) / bch_krw (비트코인 캐시)
     *
     * @return  array       $result
     *          int         $result['timestamp']        최종 체결 시각
     *          int         $result['last']             최종 체결 가격
     *          int         $result['bid']              최우선 매수호가. 매수 주문 중 가장 높은 가격
     *          int         $result['ask']              최우선 매도호가. 매도 주문 중 가장 낮은 가격
     *          int         $result['low']              (최근 24시간) 저가. 최근 24시간 동안의 체결 가격 중 가장 낮 가격
     *          int         $result['high']             (최근 24시간) 고가. 최근 24시간 동안의 체결 가격 중 가장 높은 가격
     *          int         $result['volume']           거래량
     */
    public function getDetailedTicker ($param) {
        $result = array();

        $response = $this->requestGET('v1/ticker/detailed', $param);
        if (isset($response['response']['http_code']) && $response['response']['http_code'] == 200) {
            $result = $response['json'];
        } else {}

        return $result;
    }

    /**
     * getOrderbook : Exchange Public API
     *
     * 매수/매도 호가
     *
     * @param   array       $param
     *          string      $param['currency_pair']     btc_krw (비트코인) / etc_krw (이더리움 클래식) / eth_krw (이더리움) /
     *                                                  xrp_krw (리플) / bch_krw (비트코인 캐시)
     *
     * @return  array       $result
     *          int         $result['timestamp']        가장 마지막으로 유입된 호가의 주문 유입시각
     *          array       $result['asks']             [가격, 미체결잔량]으로 구성된 개별 호가를 나열한다.
     *                                                  3번째 값은 더이상 지원하지 않고 항상 “1"로 세팅된다.
     *          array       $result['bids']             [가격, 미체결잔량]으로 구성된 개별 호가를 나열한다.
     *                                                  3번째 값은 더이상 지원하지 않고 항상 "1"로 세팅된다.
     */
    public function getOrderbook ($param) {
        $result = array();

        $response = $this->requestGET('v1/orderbook', $param);
        if (isset($response['response']['http_code']) && $response['response']['http_code'] == 200) {
            $result = $response['json'];
        } else {}

        return $result;
    }

    /**
     * getTransactions : Exchange Public API
     *
     * 체결 내역
     *
     * @param   array       $params
     *          string      $param['currency_pair']     btc_krw (비트코인) / etc_krw (이더리움 클래식) / eth_krw (이더리움) /
     *                                                  xrp_krw (리플) / bch_krw (비트코인 캐시)
     *          string      $params['time']             minute 인 경우 최근 1분, hour 인 경우 최근 1시간, day 인 경우는 최근 1일의 체결 데이터를 요청.
     *
     * @return  array       $result
     *          int         $result[]['timestamp']      체결 시각
     *          int         $result[]['tid']            체결 일련 번호
     *          int         $result[]['price']          체결 가격
     *          int         $result[]['amount']         체결 수량
     */
    public function getTransactions ($params) {
        $result = array();

        $response = $this->requestGET('v1/transactions', $params);
        if (isset($response['response']['http_code']) && $response['response']['http_code'] == 200) {
            $result = $response['json'];
        } else {}

        return $result;
    }

    /**
     * getConstants : Exchange Public API
     *
     * 각종 제약조건
     *
     * @return  array       $result
     *          int         $result['krwWithdrawalFee']     원화 환급 수수료 ( 1,000 KRW )
     *          int         $result['maxKrwWithdrawal']     원화 하루 최대 출금 가능액 ( 10,000,000 KRW )
     *          int         $result['minKrwWithdrawal']     원화 최소 출금가능액 ( 1,000 KRW )
     *          int         $result['btcTickSize']          BTC 호가 단위 ( 500 KRW )
     *          int         $result['btcWithdrawalFee']     비트코인 전산망 수수료 ( 0.0005 BTC )
     *          int         $result['maxBtcOrder']          비트코인 매수/매도 수량 최대 입력값 ( 100 BTC )
     *          int         $result['maxBtcPrice']          주문가 (1BTC가격) 최대 입력값 ( 100,000,000 KRW )
     *          int         $result['minBtcOrder']          비트코인 매수/매도 수량 최소 입력값 ( 0.01 BTC )
     *          int         $result['minBtcPrice']          주문가 (1BTC가격) 최소 입력값 ( 1,000 KRW )
     *          int         $result['maxBtcWithdrawal']     비트코인 출금 최대 입력값 ( 3 BTC )
     *          int         $result['minBtcWithdrawal']     비트코인 출금 최소 입력값 ( 0.0001 BTC )
     *          int         $result['etcTickSize']          이더리움 클래식 호가 단위( 10 KRW )
     *          int         $result['maxEtcOrder']          이더리움 클래식 매수/매도 수량 최대 입력값 ( 5,000 ETC )
     *          int         $result['maxEtcPrice']          주문가 (1ETC가격) 최대 입력값 ( 100,000,000 KRW )
     *          int         $result['minEtcOrder']          이더리움 클래식 매수/매도 수량 최소 입력값 ( 0.1 ETC )
     *          int         $result['minEtcPrice']          주문가 (1ETC가격) 최소 입력값 ( 100 KRW )
     *          int         $result['ethTickSize']          이더리움 호가 단위 ( 50 KRW )
     *          int         $result['maxEthOrder']          이더리움 매수/매도 수량 최대 입력값 ( 20,000 ETH )
     *          int         $result['maxEthPrice']          주문가 (1ETH가격) 최대 입력값 ( 100,000,000 KRW )
     *          int         $result['minEthOrder']          이더리움 매수/매도 수량 최소 입력값 ( 0.5 ETH )
     *          int         $result['minEthPrice']          주문가 (1ETH가격) 최소 입력값 ( 1,000 KRW )
     *          int         $result['minTradableLevel']     2 등급
     */
    public function getConstants () {
        $result = array();

        $response = $this->requestGET('v1/constants');
        if (isset($response['response']['http_code']) && $response['response']['http_code'] == 200) {
            $result = $response['json'];
        } else {}

        return $result;
    }

    /**
     * getAccessToken : Authentication API
     *
     * 인증
     *
     * @param   array       $params
     *          string      $params['username']     E-Mail
     *          string      $params['password']     password
     *
     * @return  string      $token
     */
    public function getAccessToken ($params) {
        $token = '';
        $time = time();
        $response = array();

        if (isset($this->access_token['access_token']) && $this->access_token['expires_time'] > $time) {
            if ($this->access_token['expires_time'] > ($time + 60)) {
                $token = $this->access_token['access_token'];
            } else {
                $params['client_id'] = $this->api_key;
                $params['client_secret'] = $this->api_secret;
                $params['refresh_token'] = $this->access_token['refresh_token'];
                $params['grant_type'] = 'refresh_token';

                if (isset($_SESSION['KorbitToken'])) unset($_SESSION['KorbitToken']);

                $response = $this->requestPOST('v1/oauth2/access_token', $params);
            }
        } else {
            $params['client_id'] = $this->api_key;
            $params['client_secret'] = $this->api_secret;
            $params['grant_type'] = 'password';

            if (isset($_SESSION['KorbitToken'])) unset($_SESSION['KorbitToken']);

            $response = $this->requestPOST('v1/oauth2/access_token', $params);
        }

        if (isset($response['response']['http_code']) && $response['response']['http_code'] == 200) {
            $result = $response['json'];

            if (isset($result['access_token'])) {
                $token = $result['access_token'];
                $result['expires_time'] = $time + $result['expires_in'];
                $this->access_token = $_SESSION['KorbitToken'] = $result;
            }
        } else {}

        return $token;
    }

    /**
     * refreshAccessToken : Authentication API
     *
     * 인증
     *
     * @param   array       $params
     *          string      $params['username']     E-Mail
     *          string      $params['password']     password
     * @param   array       $token
     *
     * @return  array       $result
     */
    public function refreshAccessToken ($params, $token = array()) {
        $time = time();
        $result = array();
        $response = array();

        if (isset($token['token_type'],$token['access_token'],$token['expires_in'],$token['expires_time'],$token['refresh_token']) &&
            $token['expires_time'] > $time) {

            if ($token['expires_time'] > ($time + 60)) {
                $result = $this->access_token = $token;
            } else {
                $params['client_id'] = $this->api_key;
                $params['client_secret'] = $this->api_secret;
                $params['refresh_token'] = $token['refresh_token'];
                $params['grant_type'] = 'refresh_token';

                $response = $this->_post('v1/oauth2/access_token', $params);
            }
         } else {
            $params['client_id'] = $this->api_key;
            $params['client_secret'] = $this->api_secret;
            $params['grant_type'] = 'password';

            $response = $this->requestPOST('v1/oauth2/access_token', $params);
        }

        if (isset($response['response']['http_code']) && $response['response']['http_code'] == 200) {
            $result = $response['json'];

            if (isset($result['access_token'])) {
                $result['expires_time'] = $time + $result['expires_in'];
                $this->token = $result;
            }
        } else {}

        return $result;
    }

    /**
     * getUserInfo : Authentication API
     *
     * 사용자 정보 가져오기
     *
     * @return  array       $result
     *          string      $result['email']                              사용자의 이메일 주소
     *          string      $result['nameCheckedAt']                      본인인증 완료시각. 이 필드가 없으면 아직 본인인증되지 않은 사용자이다.
     *          string      $result['name']                               사용자의 본인 인증 된 이름
     *          string      $result['phone']                              본인 인증시 사용한 휴대전화
     *          string      $result['birthday']                           본인 인증 결과로 받은 생년월일
     *          string      $result['gender']                             본인 인증 결과로 받은 셩별. m:남성, f:여성
     *          bool        $result['prefs']['notifyTrades']              체결 내역 알림 받기 여부
     *          bool        $result['prefs']['notifyDepositWithdrawal']   KRW, BTC의 입출금 내역 알림 받기 여부
     *          int         $result['userLevel']                          유저 등급
     */
    public function getUserInfo () {
        $result = array();

        if (isset($this->access_token['access_token'])) {
            $response = $this->requestGET('v1/user/info');
            if (isset($response['response']['http_code']) && $response['response']['http_code'] == 200) {
                $result = $response['json'];
            }
            else {}
        }

        return $result;
    }

    /**
     * placeBidOrder : Exchange User API
     *
     * 매수 주문
     *
     * @param   array       $params
     *          string      $param['currency_pair']     btc_krw (비트코인) / etc_krw (이더리움 클래식) / eth_krw (이더리움) /
     *                                                  xrp_krw (리플) / bch_krw (비트코인 캐시)
     *          string      $params['type']             주문 형태. "limit” : 지정가 주문, “market” : 시장가 주문.
     *          int         $params['price']            비트코인의 가격(원화). 500원 단위로만 가능하다. 지정가 주문(type=limit)인 경우에만 유효하다.
     *                                                  ETH는 50원 단위, ETC는 10원 단위로 가격을 설정할 수 있다.
     *          int         $params['coin_amount']      매수하고자 하는 코인의 수량.
     *                                                  정가 주문인 경우에는 해당 수량을 price 파라미터에 지정한 가격으로 구매하는 주문을 생성한다.
     *                                                  시장가 주문인 경우에는 해당 수량을 시장가에 구매하는 주문을 생성하며,
     *                                                  price 파라미터와 fiat_amount 파라미터는 사용되지 않는다.
     *          int         $params['fiat_amount']      코인을 구매하는데 사용하고자 하는 금액(원화).
     *                                                  시장가 주문(type=market)인 경우에만 유효하며,
     *                                                  이 파라미터를 사용할 경우 price 파라미터와 coin_amount 파라미터는 사용할 수 없다.
     *
     * @return  array       $result
     *          int         $result['orderId']          접수된 주문 ID
     *          string      $result['status']           성공이면 “success”, 실패할 경우 에러 심블이 세팅된다.
     *                                                  ERROR SYMBOLS:
     *                                                    name_unchecked      본인인증을 하지 않은 사용자가 주문을 넣은 경우.
     *                                                                        (주문은 본인 인증 한 사용자만 넣을 수 있다.)
     *                                                    under_age           19세 미만 사용자가 매수주문을 하는 경우.
     *                                                    not_enough_krw      KRW 잔고가 부족하여 매수주문을 넣을 수 없는 경우.
     *                                                    too_many_orders     사용자 당 최대 주문 건수를 초과한 경우.
     *                                                    save_failure        기타 다른 이유로 주문이 들어가지 않은 경우. 일반적으로 발생하지 않음.
     *          string      $result['currency_pair']    해당 주문에 사용된 거래 통화
     */
    public function placeBidOrder ($params) {
        $result = array();

        if (isset($this->access_token['access_token'])) {
            $response = $this->requestPOST('v1/user/orders/buy', $params);
            if (isset($response['response']['http_code']) && $response['response']['http_code'] == 200) {
                $result = $response['json'];
            }
            else {}
        }

        return $result;
    }

    /**
     * placeAskOrder : Exchange User API
     *
     * 매도 주문
     *
     * @param   array       $params
     *          string      $params['currency_pair']    btc_krw (비트코인) / etc_krw (이더리움 클래식) / eth_krw (이더리움) /
     *                                                  xrp_krw (리플) / bch_krw (비트코인 캐시)
     *          string      $params['type']             주문 형태. "limit” : 지정가 주문, “market” : 시장가 주문.
     *          int         $params['price']            비트코인의 가격(원화). 500원 단위로만 가능하다. 지정가 주문(type=limit)인 경우에만 유효하다.
     *                                                  현재 베타 서비스로 ETH는 50원 단위, ETC는 10원 단위로 가격을 설정할 수 있다.
     *          int         $params['coin_amount']      매수하고자 하는 코인의 수량.
     *                                                  정가 주문인 경우에는 해당 수량을 price 파라미터에 지정한 가격으로 구매하는 주문을 생성한다.
     *                                                  시장가 주문인 경우에는 해당 수량을 시장가에 구매하는 주문을 생성하며,
     *                                                  price 파라미터와 fiat_amount 파라미터는 사용되지 않는다.
     *
     * @return  array       $result
     *          int         $result['orderId']          접수된 주문 ID
     *          string      $result['status']           성공이면 “success”, 실패할 경우 에러 심블이 세팅된다.
     *                                                  ERROR SYMBOLS:
     *                                                    name_unchecked      본인인증을 하지 않은 사용자가 주문을 넣은 경우.
     *                                                                        (주문은 본인 인증 한 사용자만 넣을 수 있다.)
     *                                                    under_age           19세 미만 사용자가 매수주문을 하는 경우.
     *                                                    not_enough_btc      BTC 잔고가 부족하여 매도주문을 넣을 수 없는 경우.
     *                                                    too_many_orders     사용자 당 최대 주문 건수를 초과한 경우.
     *                                                    save_failure        기타 다른 이유로 주문이 들어가지 않은 경우. 일반적으로 발생하지 않음.
     *          string      $result['currency_pair']    해당 주문에 사용된 거래 통화
     */
    public function placeAskOrder ($params) {
        $result = array();

        if (isset($this->access_token['access_token'])) {
            $response = $this->requestPOST('v1/user/orders/sell', $params);
            if (isset($response['response']['http_code']) && $response['response']['http_code'] == 200) {
                $result = $response['html'];
            }
            else {}
        }

        return $result;
    }

    /**
     * cancelOpenOrders : Exchange User API
     *
     * 주문 취소
     *
     * @param   array       $params
     *          string      $params['currency_pair']    btc_krw (비트코인) / etc_krw (이더리움 클래식) / eth_krw (이더리움) /
     *                                                  xrp_krw (리플) / bch_krw (비트코인 캐시)
     *          int         $params['id']               취소할 주문의 ID.
     *                                                  여러 건의 주문을 한 번에 취소할 수 있도록 id가 여러 번 올 수 있으며,
     *                                                  v1/user/orders/open의 응답에 들어있는 id 필드의 값이나,
     *                                                  v1/user/orders/buy 혹은 v1/user/orders/sell의 결과로 받은 orderId를 사용할 수 있다.
     *
     * @return  array       $result
     *          int         $result[]['orderId']        id 파라미터로 넘긴 주문 일련번호.
     *          string      $result[]['status']         성공이면 “success”, 실패할 경우 에러 심블이 세팅된다.
     *                                                  ERROR SYMBOLS:
     *                                                    under_age           19세 미만 사용자가 주문 취소를 하는 경우.
     *                                                    not_found           해당 주문이 존재하지 않는 경우. 잘못된 주문 일련번호를 지정하면 이 에러가 발생한다.
     *                                                    not_authorized      다른 사용자의 주문을 취소하려고 한 경우.
     *                                                    already_filled      취소되기 전에 주문 수량 모두 체결된 경우.
     *                                                    partially_filled    체결되지 않은 주문에 대해 주문 취소하였으나, 도중에 부분 체결된 경우.
     *                                                    already_canceled    이미 취소된 주문인 경우.
     */
    public function cancelOpenOrders ($params) {
        $result = array();

        if (isset($this->access_token['access_token'])) {
            $response = $this->requestPOST('v1/user/orders/cancel', $params);
            if (isset($response['response']['http_code']) && $response['response']['http_code'] == 200) {
                $result = $response['json'];
            }
            else {}
        }

        return $result;
    }

    /**
     * listOpenOrders : Exchange User API
     *
     * 미 체결 주문내역
     *
     * @param   array       $params
     *          string      $params['currency_pair']    btc_krw (비트코인) / etc_krw (이더리움 클래식) / eth_krw (이더리움) /
     *                                                  xrp_krw (리플) / bch_krw (비트코인 캐시)
     *          int         $params['offset'] /         전체 데이터 중 offset(0부터 시작) 번 째 데이터부터 limit개를 가져오도록 지정 가능하다.
     *                      $params['limit']            offset의 기본값은 0이며, limit의 기본값은 10이다.
     *
     * @return  array       $result
     *          int         $result[]['timestamp']      주문 유입 시각
     *          int         $result[]['id']             주문 일련번호
     *          string      $vresult[]['type']          주문 종류. “bid"는 매수주문, "ask"은 매도주문
     *          int         $result[]['price']          주문가격. price.value로 주문 가격이 들어온다.
     *                                                  이후 원화 이외의 통화로 거래하도록 허용할 경우에 대비하여 currency 구분을 두도록 하였으나,
     *                                                  지금은 항상 krw로 세팅된다.
     *          int         $result[]['total']          주문한 BTC 수량. 이 필드 아래에 currency와 value 필드가 온다.
     *                                                  currency는 항상 ‘btc'로 들어오며, value에는 주문한 BTC 수량이 들어온다.
     *          int         $result[]['open']           주문한 BTC 수량 중 아직 체결되지 않은 수량.
     *                                                  이 필드 아래에 currency와 value 필드가 온다.
     *                                                  currency는 항상 'btc'로 들어오며, value에는 아직 체결되지 않은 BTC 수량.
     */
    public function listOpenOrders ($params) {
        $result = array();

        if (isset($this->access_token['access_token'])) {
            $response = $this->requestGET('v1/user/orders/open', $params);
            if (isset($response['response']['http_code']) && $response['response']['http_code'] == 200) {
                $result = $response['json'];
            }
            else {}
        }

        return $result;
    }

    /**
     * viewExchangeOrders : Exchange User API
     *
     * 거래소 주문 조회
     *
     * @param   array       $params
     *          string      $params['currency_pair']    btc_krw (비트코인) / etc_krw (이더리움 클래식) / eth_krw (이더리움) /
     *                                                  xrp_krw (리플) / bch_krw (비트코인 캐시)
     *          string      $params['status']           Optional parameter for filtering by order status.
     *                                                  'unfilled’, 'partially_filled’, 'filled’ 값 중의 하나로, 주문 상태에 따라 조회할 수 있다.
     *                                                  여러 상태를 조합하여 status=unfilled&status=partially_filled 와 같은 복합적인 콜을 만들어 낼 수 있다.
     *          int         $params['id']               Optional parameter for querying by order id.
     *                                                  주문의 ID로 조회할 수 있다. 여러 id를 조합하여 id=90308&id=90374 와 같은 복합적인 콜을 만들어 낼 수 있다.
     *          int         $params['offset']           Optional parameter for pagination.
     *                                                  전체 데이터 중 offset(기본값은 0)번째부터 데이터를 가져오도록 지정할 수 있다. 기본값은 0이다.
     *          int         $params['limit']            Optional parameter for pagination.
     *                                                  전체 데이터 중 limit(기본값은 40)개만 가져오도록 지정할 수 있다.
     *
     * @return  array       $result
     *          int         $result['id']               주문의 ID 식별번호
     *          string      $result['currency_pair']    주문이 이루어진 화폐 단위
     *          string      $result['side']             주문의 매매 종류. 매수 주문일 시에는 'bid’, 매도 주문일 시에는 'ask'가 나오게 된다.
     *          int         $result['avg_price']        현재까지 체결된 주문에 대한 가격의 가중평균치
     *          int         $result['price']            주문 시에 설정한 지정가. 시장가 주문일 경우에는 기본값인 0으로 나오게 된다.
     *          int         $result['order_amount']     주문 시에 지정한 코인의 수량
     *          int         $result['field_amount']     현재까지 체결된 코인의 수량. filledAmount와 orderAmount가 같을 때 주문이 체결 완료된다.
     *          int         $result['order_total']      원화(KRW) 기준 주문 금액. 시장가 매도 주문의 경우 이 필드는 표시되지 않는다.
     *          int         $result['field_total']      원화(KRW) 기준 체결 금액
     *          int         $result['created_at']       거래를 주문한 시각. Unix timestamp(milliseconds)로 제공된다.
     *          int         $result['last_filled_at']   거래가 부분 체결된 최종 시각. Unix timestamp(milliseconds)로 제공된다.
     *                                                  부분적으로도 전혀 체결되지 않은 주문(unfilled)에서는 이 필드는 표시되지 않는다.
     *          string      $result['status']           현재 주문의 상태. 상태에 따라 'unfilled’, 'partially_filled’, 'filled’ 값으로 나오게 된다.
     *          double      $result['fee']              거래 수수료. 매수 주문일 시에는 해당 매수 코인으로 수수료가 적용되며,
     *                                                  매도 주문일 시에는 원화(KRW)로 수수료가 적용된다.
     *                                                  부분적으로도 전혀 체결되지 않은 주문(unfilled)에서는 이 필드는 표시되지 않는다.
     */
    public function viewExchangeOrders ($params) {
        $result = array();

        if (isset($this->access_token['access_token'])) {
            $response = $this->requestGET('v1/user/orders', $params);
            if (isset($response['response']['http_code']) && $response['response']['http_code'] == 200) {
                $result = $response['json'];
            }
            else {}
        }

        return $result;
    }

    /**
     * viewTransfers : Exchange User API
     *
     * 입출금 내역 조회
     *
     * @param   array       $params
     *          string      $params['currency']         입출금 내역을 확인하고자 하는 거래 통화. 현재 KRW, BTC, ETH, ETC, XRP를 지원한다.
     *          string      $params['type']             Optional parameter for filtering.
     *                                                  입출금의 종류로, 입금(deposit) 또는 출금(withdrawal)으로 파라미터를 설정할 수 있다.
     *                                                  기본값은 입출금(all)로, 입금 및 출금 내역을 모두 조회할 수 있다.
     *          int         $params['offset']           Optional parameter for pagination.
     *                                                  전체 데이터 중 offset(기본값은 0)번째부터 데이터를 가져오도록 지정할 수 있다. 기본값은 0이다.
     *          int         $params['limit']            Optional parameter for pagination.
     *                                                  전체 데이터 중 limit(기본값은 10)개만 가져오도록 지정할 수 있다. 기본값은 40이다.
     *
     * @return  array       $result
     *          int         $result['id']               주문의 ID 식별번호
     *          string      $result['type']             입출금의 종류로 입금(deposit) 또는 출금(withdrawal)이 나오게 된다.
     *          string      $result['currency']         주문이 이루어진 화폐 단위
     *          int         $result['amount']           입출금된 화폐의 수량
     *          int         $result['completed_at']     입출금이 완료된 시각. 입출금이 완료되지 않았을 때는 이 필드는 표시되지 않는다.
     *                                                  Unix timestamp(milliseconds)로 제공된다.
     *          int         $result['updated_at']       입출금 주문이 새로이 갱신된 시각. 이 필드값을 기준으로 입출금 항목이 최신순으로 정렬되어 리턴된다.
     *                                                  Unix timestamp(milliseconds)로 제공된다.
     *          int         $result['created_at']       입출금이 주문된 시각. Unix timestamp(milliseconds)로 제공된다.
     *          int         $result['status']           현재 입출금 주문의 상태
     *          int         $result['fee']              출금액에서 차감된 출금수수료. 수수료의 화폐 단위는 출금된 화폐와 같다.
     *                                                  수수료가 발생한 경우에만 이 필드가 표시된다.
     *          array       $result['details']          SUB-FIELDS (코인 입출금시)
     *                                                    string  $result['details']['transaction_id'] : 코인의 거래 ID
     *                                                    string  $result['details']['address'] : 코인의 예금주
     *                                                    int     $result['details']['destination_tag'] : 코인이 XRP인 경우에만 표시되는 destination tag
     *                                                  SUB-FIELDS (원화 입출금시)
     *                                                    string  $result['details']['bank'] : 거래에 사용된 은행의 이름
     *                                                    int     $result['details']['account_number'] : 거래에 사용된 계좌번호
     *                                                    string  $result['details']['details.owner'] : 예금주
     */
    public function viewTransfers ($params) {
        $result = array();

        if (isset($this->access_token['access_token'])) {
            $response = $this->requestGET('v1/user/transfers', $params);
            if (isset($response['response']['http_code']) && $response['response']['http_code'] == 200) {
                $result = $response['json'];
            }
            else {}
        }

        return $result;
    }

    /**
     * viewTransactionsHistory : Exchange User API
     *
     * 체결된 주문 내역
     *  이 기능은 거래소 주문 조회와 입출금 내역 조회 기능으로 세분화되었다.
     *  기존 API 사용자의 편의를 위하여 본 기능은 제한적이나마 운영되지만, 신규 기능을 사용하는 것이 권장된다.
     *
     * @param   array       $params
     *          string      $params['currency_pair']    btc_krw (비트코인) / etc_krw (이더리움 클래식) / eth_krw (이더리움) /
     *                                                  xrp_krw (리플) / bch_krw (비트코인 캐시)
     *          string      $params['category']         fills(체결), fiats(KRW 입출금), coins(BTC 입출금)중 하나를 지정할 수 있으며
     *                                                  다른 카테고리를 여러 번 반복하는 것이 가능하다.
     *                                                  예를 들어, category=fiats&category=coins 와 같은 조합으로 요청하는 것이 가능하다.
     *                                                  기본 값은 세 가지를 모두 받도록 한다.
     *          int         $params['offset'] /         전체 데이터 중 offset(0부터 시작) 번 째 데이터부터 limit개를 가져오도록 지정 가능하다.
     *                      $params['limit']            offset의 기본값은 0이며, limit의 기본값은 10이다.
     *          int         $params['order_id']         category가 fills일 때만 유효하며, 특정 주문에 대한 체결을 가져올 때 사용한다.
     *                                                  여러 건의 주문에 대한 체결을 여러 건 조회하는 것이 가능하며,
     *                                                  해당 주문 ID를 여러 번 지정하기 위해서는 order_id=1&order_id=2와 같이
     *                                                  order_id 파라미터를 여러 번 반복하면 된다.
     *
     * @return  array       $result
     *          int         $result['timestamp']        체결, 입출금이 발생한 시각
     *          int         $result['completedAt']      완료 시각. 체결의 경우 항상 세팅되며, KRW입출금, BTC입출금의 경우나
     *                                                  아직 완료되지 않은 상태로 처리 중인 경우 이 필드가 들어오지 않는다.
     *          int         $result['id']               고유 일련번호. 각 카테고리 안에서만 일련번호가 고유하다.
     *                                                  예를 들어, 체결내역 안에서의 일련번호는 고유하지만, 체결내역과 KRW입출금 내역 간에는
     *                                                  동일한 일련번호가 사용될 수 있다.
     *          string      $result['type']             체결된 매매 종류를 나타낸다.
     *                                                  매수 주문 체결일 시에는 'buy’, 매도 주문 체결일 시에는 'sell'이 나오게 된다.
     *          array       $result['fee']              category=fills인 경우에는 매수자, 매도자가 각각 부담한 수수료.
     *                                                  BTC 매수 측의 수수료는 currency가 "btc"로, BTC 매도 측의 수수료는 currency가 "krw"로 들어온다.
     *                                                  category=fiats이며, type=fiat-out인 경우에는 원화 출금 수수료가 currency=krw로 세팅된다.
     *                                                  category=coins이며, type=coin-out인 경우에는 비트코인 출금 수수료가 currency=btc로 세팅된다.
     *                                                  그 외 다른 경우에는 이 필드가 응답에 포함되지 않는다.
     *          array       $result['fillsDetails']     체결된 가격, 수량 등의 자세한 정보
     *                      SUB-FIELDS
     *                      array  $result['fillsDetails']['price']             체결된 가격이며, currency와 value 필드가 들어있다.
     *                                                                          현재는 currency가 항상 krw로 들어온다.
     *                      array  $result['fillsDetails']['amount']            체결된 수량이며, currency와 value 필드가 들어있다.
     *                                                                          currency 필드 값에는 currency_pair에 따라 "btc”, “etc” 혹은 “eth"로 들어오며,
     *                                                                          value 필드에는 선택 화폐의 체결된 수량이 들어온다.
     *                      array  $result['fillsDetails']['native_amount']     체결된 가격과 수량을 계산한 총 거래된 액수
     *                      int    $result['fillsDetails']['orderId']           원 주문의 ID.
     *                                                                          해당 체결 건이 발생하기 전에 사용자가 실행한 주문의 ID이다.
     */
    public function viewTransactionsHistory ($params) {
        $result = array();

        if (isset($this->access_token['access_token'])) {
            $response = $this->requestGET('v1/user/transactions', $params);
            if (isset($response['response']['http_code']) && $response['response']['http_code'] == 200) {
                $result = $response['json'];
            }
            else {}
        }

        return $result;
    }

    /**
     * getTradingVolumeFee : : Exchange User API
     *
     * 거래량과 거래 수수료
     *
     * @param   array       $params
     *          string      $params['currency_pair']    btc_krw (비트코인) / etc_krw (이더리움 클래식) / eth_krw (이더리움) /
     *                                                  xrp_krw (리플) / bch_krw (비트코인 캐시) / all (모든 통화 거래소)
     * @return  array       $result
     *          array       $result['currency_pair']    해당 거래량과 거래 수수료의 거래소 통화.
     *                      SUB-FIELDS
     *                      int     $result['currency_pair']['volume']      해당 거래소 내에서의 30일간의 거래량(KRW)
     *                      double  $result['currency_pair']['maker_fee']   베이시스 포인트(BPS - 1/100 퍼센트 기준)로 표기된 maker 거래 수수료율.
     *                      double  $result['currency_pair']['taker_fee']   베이시스 포인트(BPS - 1/100 퍼센트 기준)로 표기된 taker 거래 수수료율.
     *          int         $result['total_volume']     모든 거래소의 거래량 총합 (KRW)
     *          int         $result['timestamp']        최종 거래량 및 거래 수수료 산정 시각 (매시간에 한번씩 갱신)
     */
    public function getTradingVolumeFee ($params) {
        $result = array();

        if (isset($this->access_token['access_token'])) {
            $response = $this->requestGET('v1/user/volume', $params);
            if (isset($response['response']['http_code']) && $response['response']['http_code'] == 200) {
                $result = $response['json'];
            }
            else {}
        }

        return $result;
    }

    /**
     * getWalletBallances : Wallet API
     *
     * 지갑 잔고 조회
     *
     * @return  array       $result
     *          array       $result['currency']         지갑 잔고를 확인하고자 하는 거래 통화. 현재 KRW, BTC, ETH, ETC, XRP를 지원한다.
     *                                                  KRW는 'krw', BTC는 'btc', ETH는 'eth', ETC는 'etc', XRP는 'xrp' 이다.
     *                      SUB-FIELDS
     *                      double  $result['currency']['available']            현재 거래 가능한 화폐의 수량
     *                      double  $result['currency']['trade_in_use']         현재 거래중인 화폐의 수량
     *                      double  $result['currency']['withdrawal_in_use']    현재 출금이 진행중인 화폐의 수량
     */
    public function getWalletBallances () {
        $result = array();

        if (isset($this->access_token['access_token'])) {
            $response = $this->requestGET('v1/user/balances');
            if (isset($response['response']['http_code']) && $response['response']['http_code'] == 200) {
                $result = $response['json'];
            }
            else {}
        }

        return $result;
    }


    /**
     * getWalletAccounts : Wallet API
     *
     * 지갑 정보 조회
     *
     * @return  array       $result
     *          array       $result['account']                      계좌 형태. 입금 계좌인 경우 'deposit'.
     *          array       $result['account']['currency']          입금 주소를 확인하고자 하는 거래 통화
     *                      SUB-FIELDS (일반 가상 화폐)
     *                      string  $result['account']['currency']['address']           해당 화폐의 입금 주소
     *                      SUB-FIELDS (리플인 경우)
     *                      string  $result['account']['currency']['address']           해당 화폐의 입금 주소
     *                      string  $result['account']['currency']['destination_tag']   해당 화폐의 입금 주소 태그
     *                      SUB-FIELDS (원화 계좌인 경우)
     *                      string  $result['account']['currency']['bank_name']         등록된 은행의 이름
     *                      string  $result['account']['currency']['account_number']    등록된 은행의 계좌 번호
     *                      string  $result['account']['currency']['account_name']      등록된 은행 계좌의 예금주
     */
    public function getWalletAccounts () {
        $result = array();

        if (isset($this->access_token['access_token'])) {
            $response = $this->requestGET('v1/user/accounts');
            if (isset($response['response']['http_code']) && $response['response']['http_code'] == 200) {
                $result = $response['json'];
            }
            else {}
        }

        return $result;
    }

}

?>