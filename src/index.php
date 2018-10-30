<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
    <title>Document</title>
    <style type='text/css'>
        tbody {
            background-color: #c1c2ed;
        }

        .hdr {
            background-color: #FFEB3B;
            box-shadow: inset -2px 2px 5px grey;
        }

        .data {
            background-color: gainsboro;
            box-shadow: inset -2px 2px 5px grey;
        }
    </style>
</head>
<body>
<?php
$_BLOCKCHAINNAME = "@@BLOCKCHAINNAME@@";
$_BLOCKCHAIND = "@@GROESTLCOIND@@";
$qry = [];
$_REURL = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
parse_str($_SERVER['QUERY_STRING'], $qry);

if (isset($qry["block"])) {
    $useblock = $qry["block"];
}
if (isset($qry["height"])) {
    $useheight = $qry["height"];
}
if (isset($qry["tx"])) {
    $usetx = $qry["tx"];
}
if (isset($qry["address"])) {
    $useaddress = $qry["address"];
}
if (isset($qry["top20"])) {
    $usetop20 = $qry["top20"];
}
if (isset($qry["total"])) {
    $usetotal = $qry["total"];
}

if (NULL != $usetotal) {
    $input = json_decode(
        '{ "as of": "' . date(DATE_ISO8601) . '", "coins": ' . json_decode(`curl "@@COUCHPREFIX@@/@@SYM@@_blocks/_design/address/_view/value?reduce=true&grouping=none" `)->rows[0]->value . "}");

} else
    if (NULL != $usetop20) {
        $input = json_decode(`./top20.sh`);
    } else
        if (NULL != $useaddress) {
            $amt = json_decode(`curl "@@COUCHPREFIX@@/@@SYM@@_blocks/_design/address/_view/value?reduce=true&key=%22$useaddress%22" `);
            $cnt = json_decode(`curl "@@COUCHPREFIX@@/@@SYM@@_blocks/_design/address/_view/counter?group=true&reduce=true&key=%22$useaddress%22" `);
            $input = json_decode(json_encode(["${useaddress}" => $amt->rows[0]->value,
                "transactions" => $cnt->rows[0]->value]));

        } else if (NULL != $usetx) {
            $json = `${_BLOCKCHAIND} gettransaction ${usetx}`;
            $input = json_decode($json);
        } else {
            if (NULL != $useheight) {
                $json = `${_BLOCKCHAIND} getblockbynumber ${useheight} true     `;
            } else {
                if (NULL == $useblock) {
                    $useblock = `${_BLOCKCHAIND} getbestblockhash  `;
                    `./update.sh`;
                }
                $json = `${_BLOCKCHAIND} getblock ${useblock}  true  `;
            }
            $input = json_decode($json);
        }
?>

<table>
    <?php
    echo html_table_write($input, "toplevel");
    ?>
</table>
<?php
echo <<<TAG
<p><a href=${_REURL}?top20=1> top 20 users</a>
<p><a href=${_REURL}?total=1> total issued</a>
<p><a href=${_REURL}?>most recent update</a>
TAG;
?>

<?php
function omitLabel($label)
{
    switch ($label) {
        case "n":
        case "asm":
        case "hex":
            return true;
        default :
            break;
    }
    return false;
}

/**
 * @param $inputObject
 */
function html_table_write($inputObject, $label)
{
    if (omitLabel($label)) {
        return "";
    }
    $typ = gettype($inputObject);

    $accum = "";
    switch ($typ) {
        case "array":
            $accum .= "<td><table>";
            foreach ($inputObject as $val) {
                $accum .= "<tr>" . html_table_write($val, $label) . "</tr> ";
            }
            $accum .= "</table></td>";
            break;
        case "object":
            $accum .= "<td style='border: thin black;'><table>";
            foreach ($inputObject as $key => $val) {
                $accum .= "<tr><th class='hdr'>$key</th>" . html_table_write($val, $key) . "</tr>";
            }
            $accum .= "</table></td>";
            break;
        case "resource":
        case "NULL":
        case "unknown type":
            error_log("punting on " . json_encode($inputObject));
        case     "boolean":
        case     "integer":
        case     "double" :
        case "string":
        default:
            $accum .= "<td class='data'>" . linkTo($label, $inputObject) . "</td>";
            break;
    }
    return $accum;
}

/**
 * this function decorates a value as an anchor, or something else useful
 * @param $label the json key
 * @param $val the json value
 * @return false|string
 */
function linkTo($label, $val)
{
    global $_REURL;
    $link = $val;
    switch ($label) {
        case "height":
            $link = "<a href=" . $_REURL . "?height=" . $val . ">$val</a>";
            break;
        case  "tx":
        case "txid":
            $link = "<a href=" . $_REURL . "?tx=$val title=$val>" . substr($val, 0, 8) . "</a>";
            break;
        case "proofhash":
        case "merkleroot":
        case "signature":
            $link = "<span title=$val>" . substr($val, 0, 8) . "...</span>";
            break;
        case "previousblockhash":
        case "nextblockhash":
        case "blockhash":
        case "hash":
            $link = "<a href=" . $_REURL . "?block=$val title=$val>" . substr($val, 0, 8) . "</a>";
            break;
        case "addresses":
            $link = "<a href=" . $_REURL . "?address=" . $val . ">$val</a>";
            break;
        case "time":
            $link = gmdate("Y-m-d\TH:i:s\Z", $val);
        default:
            break;
    }
    return $link;
}

/**
 * cribbed from stackoverflow
 *
 * @param $subject
 * @param $suffix
 * @return bool
 */
function matchSuffix($subject, $suffix)
{
    return substr($subject, -strlen($suffix)) == $suffix;
}
