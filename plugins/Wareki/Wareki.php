<?php
require_once( LIB_DIR . 'Prototype' . DS . 'class.PTPlugin.php' );

class Wareki extends PTPlugin {

    // https://ja.wikipedia.org/wiki/元号から西暦への変換表 によるデータ
    // '元号' => array( '元号のローマ字表記頭文字', '開始日', '最終日', 開始年)
    private $data = array(
        '明治' => array( 'M', '1868-01-25', '1912-07-29', 1868 ),
        '大正' => array( 'T', '1912-07-30', '1926-12-24', 1912 ),
        '昭和' => array( 'S', '1926-12-25', '1989-01-07', 1926 ),
        '平成' => array( 'H', '1989-01-08', '2019-04-30', 1989 ),
        '令和' => array( 'R', '2019-05-01', '2999-12-31', 2019 )
    );

    function __construct () {
        parent::__construct();
    }

    function post_init ( $app ) {
    }

    function modifier_convert_wareki ( $str, $args, $ctx ) {
        preg_match( '/((\d{4})-(\d{2})-(\d{2}))\s.*/', $str, $date_matches );
        preg_match( '/^(g{1,3})(e{1,2})((年|\.|-)(.*))$/u', $args, $args_matches );
        $date = strtotime( $date_matches[1] );

        // 変換できない場合は文字列をそのまま返す
        // 完全なチェックではない
        if ( !$date || count( $args_matches ) !== 6 ) {
            return $str;
        }

        // 月日と時間をフォーマットして変数に代入
        $formated_datetime = date( 'Y' . $args_matches[3], strtotime( $str ) );
        $month_day_time = preg_replace( '/^\d+(年|\.|-)/', '', $formated_datetime );

        foreach ( $this->data as $key => $value ) {
            if ( strtotime( $value[1] ) <= $date && $date <= strtotime( $value[2] ) ) {
                $wareki_year = (int)$date_matches[2] - $value[3] + 1;

                // 年の0埋め
                if ( $args_matches[2] === 'ee' && $wareki_year < 10 ) {
                    $wareki_year = '0' . $wareki_year;
                }

                // 和暦の出力
                if ( $args_matches[1] === 'ggg' ) {
                    // “元年”表記対応
                    if ( $wareki_year === 10 ) {
                        $wareki_year = '元';
                    }
                    return $key . $wareki_year . $args_matches[4] . $month_day_time;
                } else if ( $args_matches[1] === 'gg' ) {
                    return mb_substr( $key, 0, 1 ) . $wareki_year . $args_matches[4] . $month_day_time;
                } else {
                    return $value[0] . $wareki_year . $args_matches[4] . $month_day_time;
                }
            }
        }

        return $formated_datetime;
    }
}
