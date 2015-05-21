<?php

namespace IdnoPlugins\PGPEmail {

    class Main extends \Idno\Common\Plugin {

	function registerPages() {
	    
	}

	function registerEventHooks() {
	    \Idno\Core\site()->addEventHook('email/send', function(\Idno\Core\Event $event) {
		$email = $event->data()['email'];
		$body = $event->response()->getBody();
		if ($event->response()->getContentType()!='text/plain') {
		    $children = $event->response()->getChildren();
		    foreach ($children as $child) {
			if ($child->getContentType() == 'text/plain') {
			    $body = $child->getBody();
			}
		    }
		}
		
		if ($encrypt = $this->encryptto(strip_tags($body), $email->message->getTo())) {
		    
		    $email->message->setBody($encrypt, 'text/plain');
		    
		    $event->setResponse($email->message);
		}
	    });
	}

	protected function find_encryption_key($keys) {

	    $fingerprint = null;
	    foreach ($keys as $k) {

		if ((!$k['expired']) && ($k['can_encrypt']) && (!$fingerprint) && (isset($k['fingerprint']))) {
		    $fingerprint = $k['fingerprint'];
		}

		if (!$fingerprint && isset($k['subkeys'])) {
		    $fingerprint = $this->find_encryption_key($k['subkeys']);
		}
	    }

	    return $fingerprint;
	}

	protected function encryptto($message, $address) {

	    $gpg = new \gnupg();

	    if (is_array($address))
	    {
		foreach ($address as $k => $v)
		{
		    $address = $k;
		    break;
		}
	    }
	    
	    // Find keys
	    $keys = $gpg->keyinfo($address); 
	    if ($keys) {
		$fingerprint = $this->find_encryption_key($keys);

		$gpg->addencryptkey($fingerprint);

		return $gpg->encrypt($message);
	    }

	    return false;
	}

    }

}
