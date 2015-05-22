<?php

namespace IdnoPlugins\PGPEmail {

    class Main extends \Idno\Common\Plugin {

	function registerPages() {
	    
	}

	function registerEventHooks() {
	    \Idno\Core\site()->addEventHook('email/send', function(\Idno\Core\Event $event) {
		$email = $event->data()['email'];
		$body = strip_tags($event->response()->getBody());
		if ($event->response()->getContentType()!='text/plain') {
		    $children = $event->response()->getChildren();
		    foreach ($children as $child) {
			if ($child->getContentType() == 'text/plain') {
			    $body = $child->getBody();
			}
		    }
		}
		
		if ($encrypt = $this->encryptto($body, $email->message->getTo())) {
		    
		    //$email->message->setBody($encrypt, 'text/plain');
		    $message = \Swift_Message::newInstance();
		    $message->setFrom($email->message->getFrom());
		    $message->setTo($email->message->getTo());
		    $message->setSubject($email->message->getSubject());
		    $message->setBody($encrypt);
		    
		    $event->setResponse($message);
		} else {
		    \Idno\Core\site()->logging()->log('Message to ' . $email->message->getTo() . ' not encrypted, probably missing a key.', LOGLEVEL_INFO);
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
	    
	    \Idno\Core\site()->logging()->log("Found fingerprint: $fingerprint", LOGLEVEL_DEBUG);

	    return $fingerprint;
	}

	protected function encryptto($message, $address) {

	    \Idno\Core\site()->logging()->log("Encrypting to $address", LOGLEVEL_DEBUG);
	    
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
	    
	    \Idno\Core\site()->logging()->log("Problem encrypting message: " . $gpg->geterror(), LOGLEVEL_ERROR);

	    return false;
	}

    }

}
