<?php
/**
 * @package   NuCaptcha PHP clientlib
 * @author    <support@nucaptcha.com> Leap Marketing Technologies Inc
 * @license   LGPL License 2.1 (see included license.txt)
 * @link      http://www.nucaptcha.com/api/php
 */

/**
 * Pick a cluster to use.
 *
 * @see http://intra.leapmarketing.com/confluence/display/TD/Cluster+Selection+Process
 * @package LeapClient
 */
class lmcClusterPicker
{
    private function __construct() {}
    
    /**
     * Cache the DNS record to prevent multiple lookups
     * @var Array
     */
    private static $sDNSCache = Array();
    
    /**
     * Cache the cluster so it's consistent across calls
     * @var string
     */
    private static $sCluster = false;
	
	/**
	 * suffix to add to URLs that are generated. Gets set from clusterRecord
	 *
	 * @var string
	 */
	private static $sDomain = false;
    
    /**
     * Pick a cluster. First looks for an override in the config file for a
     * forceCluster, then a clusterRecord, then does a DNS lookup
     *
     * @return string
     */
    public static function PickCluster ()
    {		
		// perform the lookup
		if(false === self::$sCluster)
		{
			$clusterRecord = Leap::getClusterRecord();
		
			// Assumes only the last two pieces.. clusters.nucaptcha.com
			// because nucaptcha.com. clusters.deptest.leapmarketing.com
			// becomes leapmarketing.com. I suspect this will change in the
			// future..
			$matches = Array();
			
			if(0 == preg_match('/\.([^\.]+\.[^\.]+)$/', $clusterRecord, $matches))
			{
				// last ditch effort -- assume nucaptcha
				trigger_error("Could not parse clusterRecord $clusterRecord. Defaulting to nucaptcha.com", E_USER_WARNING);
				self::$sDomain = 'nucaptcha.com';
			}
			else
			{
				self::$sDomain = $matches[1];
			}
						
			$result = self::GetDNS($clusterRecord);
			
			// parse the values
			$clusters = Array();
				
			foreach($result as $dnsrecord)
			{
				$clusters = array_merge($clusters, self::ParseRecord($dnsrecord['txt']));
			}
				
			// no clusters found!
			if(sizeof($clusters) === 0)
			{
				return 'default';
			}
			
			// pick a cluster at random
			self::$sCluster = $clusters[rand(0, sizeof($clusters)-1)];
		}
	
		return self::$sCluster;
    }
    
    /**
     * pull the cluster record from cache or DNS
     *
     * @param string $clusterRecord
     * @return array
     */
    private static function GetDNS ($clusterRecord)
    {
		if(true === array_key_exists($clusterRecord, self::$sDNSCache))
		{
			if(time() > self::$sDNSCache[$clusterRecord]['expires'])
			{
				return self::$sDNSCache[$clusterRecord]['record'];
			}
		}

		// forced a token server, use the old method.
		if(false === lmcHelper::isDnsGetRecordSupported())
		{
			throw new Exception("dns_get_record() is not supported on this platform.");
		}

		$result = dns_get_record($clusterRecord, DNS_TXT);
		
		if(sizeof($result) == 0)
		{
			throw new LeapException('Failed DNS lookup for '. $clusterRecord . '.',	LMSC_DNSERROR);
		}
		
		self::$sDNSCache[$clusterRecord]['record'] = $result;
		self::$sDNSCache[$clusterRecord]['expires'] = time() + $result[0]['ttl'];
		
		return $result;
    }
    
    /**
     * parse a TXT record. Returns Array($name * $weight)
     *
     * FIXME this weighting is wrong -- it doesn't match the description at
     * http://intra.leapmarketing.com/confluence/display/TD/Cluster+Selection+Process
     *
     * On failure, returns an empty array
     *
     * @param string $record
     * @return array ('record', 'record', etc) based on weight
     */
    protected static function ParseRecord ($record)
    {
		$results = explode(' ', $record, 2);
		$weight  = 1;
		$matches = Array();
		
		
		// weight is correct
		if(0 == sizeof($results))
		{
			throw new LeapException("Cluster record could not be split.", LMSC_DNSERROR);
		}
		elseif('' == $results[0])
		{
			throw new LeapException("Cluster record is empty", LMSC_DNSERROR);
		}
		elseif(1 == sizeof($results))
		{
			// use default weight -- record didn't parse correctly.
			// very likely things are super corrupted, but give it a go
		}
		elseif(true === ctype_digit($results[1]))
		{
			$weight = $results[1];
		}
		// weight contains a number
		elseif(1 == preg_match('/(\d+)/', $results[1], $matches))
		{
			$weight = $matches[0];	
		}
		// otherwise, weight remains 1
		
		$weighted = Array();
		
		for($i = 0; $i < $weight; $i++)
		{
			$weighted[] = $results[0];
		}
		
		return $weighted;
    }
    
    /**
     * Get the token server for this configuration.
     *
     * @return string URL for token server
     */
    public static function GetTokenServer ()
    {
		return self::GetServer('forceTokenServer', 'token');
    }
    
    /**
     * Get the data server for this configuration.
     *
     * @return string URL for data server
     */
    public static function GetDataServer ()
    {
		return self::GetServer('forceDataServer', 'data');
    }
    
    /**
     * Get the validate server for this configuration.
     *
     * @return string URL for validate server
     */
    public static function GetValidateServer ()
    {
		return self::GetServer('forceValidateServer', 'validate');
    }
    
    /**
     * Get the resource server URL.
     *
     * @return string URL for resources
     */
    public static function GetResourceServer ()
    {		
		$resourcerecord = sprintf('resources.%s.' . self::$sDomain, self::PickCluster());

		// forced a token server, use the old method.
		if(false === lmcHelper::isDnsGetRecordSupported())
		{
			throw new Exception("dns_get_record() is not supported on this platform.");
		}

		$result = dns_get_record($resourcerecord, DNS_TXT);
		
		if(sizeof($result) === 0)
		{
			trigger_error("No TXT record found for $resourcerecord.", E_USER_WARNING);
			// FIXME probably need a default here. It currently returns the resourcerecord -- it may work?
			// but no version number
			return "http://$resourcerecord/";
		}
		
		return $result[0]['txt'];
    }
    
    /**
     * Generic function for getting a server URL
     *
     * @param string $configname
     * @param string $prefix
     * @return string
     */
    private static function GetServer ($configName, $prefix)
    {
		/*try
		{
			$server = lmcDefaultConfig::GetGlobal($configname);
			
			return $server;
		}
		catch (Exception $e) {}
		*/
	
		// most of the old force* records aren't used any more. Instead,
		// we do this:
		if('forceTokenServer' == $configName)
		{
			if(false != Leap::getForceTokenServer())
			{
				return Leap::getForceTokenServer();
			}
		}
		
		return sprintf('http://%s.%s.%s/', 
			$prefix,
			self::PickCluster(),
			self::$sDomain
		);
    }
    
    /**
     * If you're code is persistent, this method will reset the cluster
     */
    public function ClearCluster()
    {
		self::$sCluster = false;
    }
}