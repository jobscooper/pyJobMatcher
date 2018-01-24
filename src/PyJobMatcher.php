<?php
/**
 * Copyright 2014-18 Bryan Selner
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may
 * not use this file except in compliance with the License. You may obtain
 * a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations
 * under the License.
 */
namespace JobScooper\Utils;
define("__ROOT__", dirname(dirname(__FILE__)));

use Monolog;
use Monolog\Handler\StreamHandler;

class PyJobMatcher
{
	protected $_infile = null;
	protected $_outfile = null;
	/**
	 * @var \Monolog\Logger|null
	 */
	protected $_logger = null;

	/**
	 * PyJobMatcher constructor.
	 *
	 * @param                 $inputJsonPath
	 * @param                 $outputJsonPath
	 * @param \Monolog\Logger $logger
	 *
	 * @throws \Exception
	 */
	function __construct($inputJsonPath, $outputJsonPath, $logger)
	{
		$this->_infile = $inputJsonPath;
		$this->_outfile = $outputJsonPath;
		$this->_logger = $logger;

		return $this->doMatching();
	}

	/**
	 * @param $message
	 */
	private function _log($message)
	{
		if(!empty($this->_logger))
			$this->_logger->info($message);
		else
			print($message);
	}

	/**
	 * @throws \Exception
	 */
	function doMatching()
	{
		try {
			$this->_log("Calling python to do work of job title matching.");
			$PYTHONPATH = realpath(__ROOT__ . "/jobmatcher/matchTitlesToKeywords.py");
			$cmd = "python " . $PYTHONPATH . " -i " . escapeshellarg($this->_infile) . " -o " . escapeshellarg($this->_outfile);

#					$cmd = "source " . realpath(__ROOT__) . "/python/pyJobNormalizer/venv/bin/activate; " . $cmd;

			$this->_log(PHP_EOL . "    ~~~~~~ Running command: " . $cmd . "  ~~~~~~~" . PHP_EOL);
			$this->doExec($cmd);
		} catch (\Exception $ex)
		{
			throw $ex;
		}
		finally
		{
			$this->_log("Python command call finished.");
		}
	}


	/**
	 * @param $cmd
	 *
	 * @return array|mixed|string
	 */
	function doExec($cmd)
	{
		$cmdOutput = array();
		$cmdRet = "";

		exec($cmd, $cmdOutput, $cmdRet);
		foreach ($cmdOutput as $resultLine)
			$this->_log($resultLine);
		unset($resultLine);

		if (is_array($cmdOutput))
		{
			if (count($cmdOutput) >= 1)
				return $cmdOutput[0];
			else
				return "";
		}
		return $cmdOutput;
	}

}

//$handlerOut = new Monolog\Handler\StreamHandler("php://stderr", Monolog\Logger::DEBUG );
//$logger = new Monolog\Logger("debug", array($handlerOut));
//
//$outfile = "/private/var/local/jobs_scooper/output/2018-01-21/debug/pyMatcherOutTest.json";
//$matcher = new PyJobMatcher($inputJsonPath="/private/var/local/jobs_scooper/output/2018-01-21/debug/mark_titlematches_src_20180121235632.json", $outputJsonPath=$outfile, null);
//$outjson = file_get_contents($outfile);
//print($outjson);
