<?php
session_start();

$tests = array(
	//request status
	array(
		/*
		"class.function" => array(
			"input" => array(),
			"output" => array(
				array("/pathtovar=value1"),
				array("=value2", "=value2")    --> value1 OR (value2 AND value3)
			)
		)
		*/
		array(
			"function" => "status.status",
			"output" => array(array("active=1"))
		)
	),
	//test auth module
	array(
		array(
			"function" => "auth.isloggedin",
			"output" => array(
				array("=1"),
				array("=0")
			)
		),
		array(
			"function" => "auth.getuserid"
		),
		array(
			"function" => "auth.logout",
			"output" => array(array("=1"))
		),
		array(
			"function" => "auth.getuserid",
			"output" => array(array("=0"))
		),
		array(
			"function" => "auth.exists",
			"input" => array("asdf@asdf.com"),
			"output" => array(array("=1"))
		),
		array(
			"function" => "auth.getregtypes",
			array()
		),
		array(
			"function" => "auth.login",
			"input" => array("bogus@bogus.com", "test", md5("mac"), "0"),
			"output" => array(
				array("result=0")
			)
		),
		array(
			"function" => "auth.login",
			"input" => array("asdf@asdf.com", "wrongpassword", md5("mac"), "0"),
			"output" => array(
				array("result=0")
			)
		)
	),
	//test registration
	array(
		array(
			"function" => "auth.register",
			"input" => array("asdf2@asdf.com", "asdf2@asdf.com", "Test Poppetje", "w8woord", "regtypewhichdoesntexist"),
			"output" => array(array("result=0"))
		),
		array(
			"function" => "auth.register",
			"input" => array("asdf2@asdf.com", "", "Test Poppetje", "w8woord", "form"),
			"output" => array(array("result=0"))
		),
		array(
			"function" => "auth.register",
			"input" => array("asdf2@asdf.com", "asdf2@asdf", "Test Poppetje", "w8woord", "form"),
			"output" => array(array("result=0"))
		),
		array(
			"function" => "auth.register",
			"input" => array("asdf@asdf.com", "asdf@asdf.com", "Test Poppetje", "w8woord", "form"),
			"output" => array(array("result=0"))
		),
		array(
			"function" => "auth.register",
			"input" => array("asdf2@asdf.com", "asdf2@asdf.com", "", "w8woord", "form"),
			"output" => array(array("result=0"))
		)
	),
	//test auto-login function
	array(
		array(
			"function" => "auth.logout"
		),
		array(
			"function" => "auth.login",
			"input" => array("asdf@asdf.com", "test", md5("mac"), "1"),
			"output" => array(
				array("result=1", "userid=1", "username=Test Homo")
			)
		)/*,
		array(
			"function" => "auth.autologin",
			"input" => array("asdf@asdf.com", "test", md5("mac"), "1"),
			"output" => array(
				array("result=1", "userid=1", "username=Test Homo")
			)
		)*/
	),
	//login using test account
	array(
		array(
			"function" => "auth.login",
			"input" => array("asdf@asdf.com", "test", md5("mac"), "0"),
			"output" => array(
				array("result=1", "userid=1", "username=Test Homo")
			)
		)
	),
	//test MAC formatting
	array(
		array(
			"function" => "test.formatmac",
			"input" => array("0xf801514d1f96"),
			"output" => array(array("=f8:01:51:4d:1f:96"))
		),
		array(
			"function" => "test.formatmac",
			"input" => array("f801514d1f96"),
			"output" => array(array("=f8:01:51:4d:1f:96"))
		),
		array(
			"function" => "test.formatmac",
			"input" => array("f8:01:51:4d:1f:96"),
			"output" => array(array("=f8:01:51:4d:1f:96"))
		),
		array(
			"function" => "test.formatmac",
			"input" => array("f8-01-51-4d-1f-96"),
			"output" => array(array("=f8:01:51:4d:1f:96"))
		),
		array(
			"function" => "test.formatmac",
			"input" => array("f801.514d.1f96"),
			"output" => array(array("=f8:01:51:4d:1f:96"))
		),
		array(
			"function" => "test.formatmac",
			"input" => array("f801.514d.1f96a"),
			"output" => array(array("=0"))
		)
	)
);
$execute = array(0, 4, 5);

$functions = array();
$files = scandir("api");
foreach ($files as $file)
{
	if (strpos($file, ".php") !== false)
	{
		$content = file("api/".$file);
		$class = false;
		foreach ($content as $line)
		{
			if (strpos($line, "class") !== false)
			{
				$line = explode(" ", trim($line));
				$classname = strtolower($line[1]);
				$class = substr($classname, 3);
			}
			elseif ($class !== false)
			{
				if (strpos($line, "public function") !== false)
				{
					$line = explode(" ", trim($line));
					$function = strtolower($line[2]);
					$function = substr($function, 0, strpos($function, "("));
					$functions[] = "$class.$function";
				}
			}
		}
	}
}

$apiurl = "/?";
$apikey = "wgPQEWFRufqJwqfCrT6DKKUP";
$passhash = "c9e6f656f133517c4fc99a77add9efe4";
$nrfunctions = 10;
$nrinput = 10;
if (isset($_POST["pass"]))
{
	if ($passhash == md5($_POST["pass"])) $_SESSION["loggedin"] = true;
	header("Location: /apitest.php");
	exit();
}
if ($_SESSION["loggedin"] == true)
{ ?>
<style type="text/css">
* {
	padding:0;
	margin:0;
}
</style>
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js" type="text/javascript"></script>
<script type="text/javascript">
var nrfunctions = <?php echo $nrfunctions; ?>;
var nrinput = <?php echo $nrinput; ?>;
var apiurl = "<?php echo $apiurl; ?>";
var apikey = "<?php echo $apikey; ?>";

var tests = [<?php
$jstests = array();
foreach ($execute as $key)
{
	if (isset($tests[$key]))
	{
		$test = $tests[$key];
		foreach ($test as $data)
		{
			$function = $data["function"];
			$input = (isset($data["input"])) ? $data["input"]: array();
			$output = (isset($data["output"])) ? $data["output"]: array();
			
			foreach ($input as $i=>$val) $input[$i] = addslashes($val);
			foreach ($output as $i=>$val)
			{
				if (!is_array($val)) $output[$i] = array($val);
				foreach ($val as $a=>$val2) $val[$a] = addslashes($val2);
				$output[$i] = '["'.implode('", "', $val).'"]';
			}
			
			$input = '["'.implode('", "', $input).'"]';
			$output = "[".implode(', ', $output)."]";
			
			$jstests[] = "['$function', $input, $output]";
		}
	}
}
echo implode(", ", $jstests);
?>];

function handleSize() {
	var height = $("#wrapper").height();
	var h1 = 0.50 * height;
	var h2 = 0.50 * height;
	$("#div1").css("height", h1+"px");
	$("#div2").css("height", h2+"px");
}
$(document).ready(function(e) {
    showFunctions();
	handleSize();
	$(window).resize(function(e) {
		handleSize();
	});
	startTests();
});

function showFunctions() {
	var nr = $("#nrfunctions").val();
	if (nr == 1) {
		$("#functionsdiv").hide();
		$("#functiondiv").show();
	} else {
		$("#functionsdiv").show();
		$("#functiondiv").hide();
		for (var i=1;i<=nrfunctions;i++) {
			if (i <= nr) {
				$("#function"+i+"div").show();
			} else {
				$("#function"+i+"div").hide();
			}
		}
	}
	showInput();
}
function showInput() {
	var nr = $("#nrfunctions").val();
	if (nr == 1) {
		var nri = $("#nrinput").val();
		for (var a=1;a<=nrinput;a++) {
			if (a <= nri) {
				$("#functioninput"+a).show();
			} else {
				$("#functioninput"+a).hide();
			}
		}
	} else {
		for (var i=0;i<=nr;i++) {
			var nri = $("#nrinput"+i).val();
			for (var a=1;a<=nrinput;a++) {
				if (a <= nri) {
					$("#function"+i+"input"+a+"div").show();
				} else {
					$("#function"+i+"input"+a+"div").hide();
				}
			}
		}
	}
}

function doRequest() {
	var nr = $("#nrfunctions").val();
	var data = {};
	if (nr == 1) {
		data["function"] = $("#function").val();
		var nri = $("#nrinput").val();
		if (nri > 0) {
			var input = [];
			for (var a=1;a<=nri;a++) {
				input.push(encodeURIComponent($("#input"+a).val()));
			}
			data["input"] = input.join("&");
		}
	} else {
		data["nrfunctions"] = nr;
		for (var i=1;i<=nrfunctions;i++) {
			if (i <= nr) {
				data["function"+i] = $("#function"+i).val();
				var nri = $("#nrinput"+i).val();
				if (nri > 0) {
					var input = [];
					for (var a=1;a<=nri;a++) {
						input.push(encodeURIComponent($("#function"+i+"input"+a).val()));
					}
					data["input"+i] = input.join("&");
				}
			}
		}
	}
	makeRequest(data);
}

function makeRequest(data, callback) {
	data["apikey"] = apikey;
	var url = apiurl + $.param(data);
	appendConsole("URL: "+url+"\n\n");
	$.ajax(url, {
		cache: false,
		dataType: "json",
		error: function(j, t, e) {
			appendConsole("Error ("+t+"): "+e+"\nResponse:\n\n"+j.responseText+"\n");
		},
		success: function(data, t, j) {
			appendConsole(print_r(data, true));
			if (callback && typeof(callback) === "function") {
				callback(data);
			}
		}
	});
}

var testnr = 0;
function startTests() {
	testnr = 0;
	doTest();
}

function doTest() {
	if (typeof tests[testnr] != "undefined") {
		test = tests[testnr];
		func = test[0];
		input = test[1];
		output = test[2];
		
		for (var a=0;a<input.length;a++) {
			input[a] = encodeURIComponent(input[a]);
		}
		
		data = {
			"function": func,
			"input": input.join("&")
		};
		
		makeRequest(data, function(data) {
			if (typeof data["error"] == "undefined") {
				var result = false;
				if (output.length == 0) {
					result = true;
				} else {
					for (var i=0;i<output.length;i++) {
						if (output[i].length == 0) {
							result = true;
							break;
						} else if (output[i].length == 1) {
							if (checkOutputVar(data, output[i][0]) == true) {
								result = true;
								break;
							}
						} else if (output[i].length > 1) {
							var result2 = true;
							for (var a=0;a<output[i].length;a++) {
								if (checkOutputVar(data, output[i][a]) == false) {
									result2 = false;
									break;
								}
							}
							if (result2 == true) {
								result = true;
								break;
							}
						}
					}
				}
				if (result == true) {
					appendConsole("Test ok!");
					testnr++;
					doTest();
				} else {
					appendConsole("Test fail!");
					appendConsole(print_r(output, true));
				}
			}
		});
	}
}

function checkOutputVar(output, outputvalue) {
	if (outputvalue.search("=") != -1) {
		data = outputvalue.split("=");
		variable = data[0];
		value = data[1];
		
		if (variable == "") {
			if (output == value) return true;
		} else {
			var currobj = output;
			variable += "/";
			while (variable.search("/") > -1) {
				nextlevel = variable.substr(0, variable.search("/"));
				if (typeof currobj[nextlevel] != "undefined") {
					currobj = currobj[nextlevel];
					variable = variable.substr(variable.search("/") + 1);
				}
			}
			if (currobj == value) return true;
		}
	}
	return false;
}

function appendConsole(str) {
	var text = $("#console").val();
	text = str + "\n" + text;
	$("#console").val(text);
}
</script>
<div id="wrapper" style="position:fixed;left:0px;top:0px;height:100%;width:100%;">
    <div style="width:100%;;overflow:auto;position:relative" id="div1"><div style="margin:10px">
        Nr of functions: <select id="nrfunctions" onchange="showFunctions();"><?php
        for ($i=1;$i<=$nrfunctions;$i++) echo "<option value='$i'>$i</option>";
        ?></select>
        <div id="functiondiv">
            <br />
            Function: <select id="function"><?php
            foreach ($functions as $function) echo "<option value='$function'>$function</option>";
            ?></select><br />
            Nr of input fields: <select id="nrinput" onchange="showInput();"><?php
            for ($a=0;$a<=$nrinput;$a++) echo "<option value='$a'>$a</option>";
            ?></select>
            <?php for ($a=1;$a<=$nrinput;$a++) { ?>
            <div id="functioninput<?php echo $a; ?>">
                Input <?php echo $a; ?>: <input type="text" id="input<?php echo $a; ?>" style="width:500px" />
            </div>
            <?php } ?>
        </div>
        <div id="functionsdiv">
            <?php for ($i=1;$i<=$nrfunctions;$i++) { ?>
            <div id="function<?php echo $i; ?>div">
                <br />
                Function <?php echo $i; ?>: <select id="function<?php echo $i; ?>"><?php
				foreach ($functions as $function) echo "<option value='$function'>$function</option>";
				?></select><br />
                Nr of input fields: <select id="nrinput<?php echo $i; ?>" onchange="showInput();"><?php
                for ($a=0;$a<=$nrinput;$a++) echo "<option value='$a'>$a</option>";
                ?></select>
                <?php for ($a=1;$a<=$nrinput;$a++) { ?>
                <div id="function<?php echo $i; ?>input<?php echo $a; ?>div">
                    Function <?php echo $i; ?>, input <?php echo $a; ?>: <input type="text" id="function<?php echo $i; ?>input<?php echo $a; ?>" style="width:500px" />
                </div>
                <?php } ?>
            </div>
            <?php } ?>
        </div>
        <div style="position:absolute;right:100px;top:50px;">
        	<input type="button" value="Call API" onclick="doRequest();" /> | 
        	<input type="button" value="Resart Unit Tests" onclick="startTests();" />
        </div>
    </div></div>
    <div style="width:100%;" id="div2"><div style="margin:0">
        <textarea id="console" style="width:100%;height:100%"></textarea>
    </div></div>
</div>
<?php } else { ?>
<div style="text-align:center"><div style="display:inline-block">
<form action="/apitest.php" method="post">
<input type="password" name="pass" /> <input type="submit" value="Start testing" />
</form>
</div></div>
<?php } ?>
<script type="text/javascript">
function print_r (array, return_val) {
  // http://kevin.vanzonneveld.net
  // +   original by: Michael White (http://getsprink.com)
  // +   improved by: Ben Bryan
  // +      input by: Brett Zamir (http://brett-zamir.me)
  // +      improved by: Brett Zamir (http://brett-zamir.me)
  // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // -    depends on: echo
  // *     example 1: print_r(1, true);
  // *     returns 1: 1
  var output = '',
    pad_char = ' ',
    pad_val = 4,
    d = this.window.document,
    getFuncName = function (fn) {
      var name = (/\W*function\s+([\w\$]+)\s*\(/).exec(fn);
      if (!name) {
        return '(Anonymous)';
      }
      return name[1];
    },
    repeat_char = function (len, pad_char) {
      var str = '';
      for (var i = 0; i < len; i++) {
        str += pad_char;
      }
      return str;
    },
    formatArray = function (obj, cur_depth, pad_val, pad_char) {
      if (cur_depth > 0) {
        cur_depth++;
      }

      var base_pad = repeat_char(pad_val * cur_depth, pad_char);
      var thick_pad = repeat_char(pad_val * (cur_depth + 1), pad_char);
      var str = '';

      if (typeof obj === 'object' && obj !== null && obj.constructor && getFuncName(obj.constructor) !== 'PHPJS_Resource') {
        str += 'Array\n' + base_pad + '(\n';
        for (var key in obj) {
		  var type = Object.prototype.toString.call(obj[key]);
          if (type === '[object Array]' || type === '[object Object]') {
            str += thick_pad + '[' + key + '] => ' + formatArray(obj[key], cur_depth + 1, pad_val, pad_char);
          }
          else {
            str += thick_pad + '[' + key + '] => ' + obj[key] + '\n';
          }
        }
        str += base_pad + ')\n';
      }
      else if (obj === null || obj === undefined) {
        str = '';
      }
      else { // for our "resource" class
        str = obj.toString();
      }

      return str;
    };

  output = formatArray(array, 0, pad_val, pad_char);

  if (return_val !== true) {
    if (d.body) {
      this.echo(output);
    }
    else {
      try {
        d = XULDocument; // We're in XUL, so appending as plain text won't work; trigger an error out of XUL
        this.echo('<pre xmlns="http://www.w3.org/1999/xhtml" style="white-space:pre;">' + output + '</pre>');
      } catch (e) {
        this.echo(output); // Outputting as plain text may work in some plain XML
      }
    }
    return true;
  }
  return output;
}
</script>