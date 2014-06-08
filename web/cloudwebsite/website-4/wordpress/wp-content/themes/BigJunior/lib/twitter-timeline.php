<?php

/*
 * Class Name: Twitter Timeline
 * Description: Class that shows recent tweets (up to 200)
 * Version: 1.1.1
 * Author: Mohsen Heydari
 * Author URI: http://devmash.net
 */

require_once(THEME_LIB . '/includes/tmhoauth/tmhOAuth.php');
require_once(THEME_LIB . '/includes/tmhoauth/tmhUtilities.php');


class Twitter_Timeline
{

    public function TheTimeline(array $settings)
    {
        $defaults = array(
            'consumer_key' => false,
            'consumer_secret' => false,
            'user_token' => false,
            'user_secret' => false,
            'count' => 5,
            'echo' => true,
        );

        $settings = array_merge($defaults, $settings);

        $tmhOAuth = new tmhOAuth(array(
            //'curl_proxy'      => '127.0.0.1:8580',
            'consumer_key'    => $settings['consumer_key'],
            'consumer_secret' => $settings['consumer_secret'],
            'user_token'      => $settings['user_token'],
            'user_secret'     => $settings['user_secret'],
        ));

        $output = '';

        //Get screen name
        $code = $tmhOAuth->request('GET', $tmhOAuth->url('1.1/account/settings.json'));

        if($tmhOAuth->response['errno'] != 0 || $tmhOAuth->response['error'] != '' )
        {
            $output = "Error:" . PHP_EOL . $tmhOAuth->response['error'];
            return $this->TimelineOutput($settings, $output);
        }

        //Convert the response string to PHP object
        $userSettings = json_decode($tmhOAuth->response['response']);

        if($userSettings == NULL)
        {
            $output = "Error: Could not decode the twitter response. Response:" . PHP_EOL . $tmhOAuth->response['response'];
            return $this->TimelineOutput($settings, $output);
        }

        //Get user timeline
        $code = $tmhOAuth->request('GET', $tmhOAuth->url('1.1/statuses/user_timeline'), array(
            'screen_name' => $userSettings->screen_name, 'count' => $settings['count']));

        if($tmhOAuth->response['errno'] != 0 || $tmhOAuth->response['error'] != '' )
        {
            $output = "Error:" . PHP_EOL . $tmhOAuth->response['error'];
            return $this->TimelineOutput($settings, $output);
        }

        $timeline = json_decode($tmhOAuth->response['response']);

        if($timeline == NULL)
        {
            $output = "Error: Could not decode the twitter response. Response:" . PHP_EOL . $tmhOAuth->response['response'];
            return $this->TimelineOutput($settings, $output);
        }

        //Convert to HTML
        foreach($timeline as $item)
            $output .= $this->FormatTimeline($item);
        
		//I could use goto but its a bit new for current php installations 
		//so i replaced it by below member function
		return $this->TimelineOutput($settings, $output);
    }

	protected function TimelineOutput(array $settings, $output)
	{
		if($settings['echo']) 
			echo $output; 
		else
			return $output;
		
		return null;
	}
	
    protected function FormatTimeline($item)
    {
        $status = $this->ConvertReplies( $this->ConvertUrls($item->text) );

        return '<li><span>' . $status . '</span><br/><a class="link" href="http://twitter.com/' . $item->user->screen_name . '/statuses/' . $item->id_str . '">' . $this->RelativeTime($item->created_at) . '</a></li>';
    }
	
	protected function ConvertUrls($status)
	{
		return preg_replace('/((https?|s?ftp|ssh)\:\/\/[^"\s\<\>]*[^.,;\'">\:\s\<\>\)\]\!])/', '<a href="$1">$1</a>', $status);
	}

	protected function ConvertReplies($status)
	{
		return preg_replace_callback('/\B@([_a-z0-9]+)/i', array( &$this, 'ReplyRegex_Callback' ), $status);
	}

	protected function ReplyRegex_Callback($matches)
	{
		return $matches[0]{0} . '<a href="http://twitter.com/'. $matches[1] .'">'. $matches[1] .'</a>';
	}

	protected function RelativeTime($a) 
	{
		//get current timestampt
		$b = strtotime("now"); 
		//get timestamp when tweet created
		$c = strtotime($a);
		//get difference
		$d = $b - $c;
		//calculate different time values
		$minute = 60;
		$hour   = $minute * 60;
		$day    = $hour * 24;
		$week   = $day * 7;
		
		if(is_numeric($d) && $d > 0) {
			//if less then 3 seconds
			if($d < 3) return _("right now");
			//if less then minute
			if($d < $minute) return floor($d) . _(" seconds ago");
			//if less then 2 minutes
			if($d < $minute * 2) return _("about 1 minute ago");
			//if less then hour
			if($d < $hour) return floor($d / $minute) . _(" minutes ago");
			//if less then 2 hours
			if($d < $hour * 2) return _("about 1 hour ago");
			//if less then day
			if($d < $day) return floor($d / $hour) . _(" hours ago");
			//if more then day, but less then 2 days
			if($d > $day && $d < $day * 2) return _("yesterday");
			//if less then year
			if($d < $day * 365) return floor($d / $day) . _(" days ago");
			//else return more than a year
			return _("over a year ago");
		}

        return '';
	}
	
}