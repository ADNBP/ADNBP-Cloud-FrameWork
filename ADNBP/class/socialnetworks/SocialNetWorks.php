<?php
// SocialNetWorks Class v10
if (!defined ("_socialNetWorks_CLASS_") ) {
    define("_socialNetWorks_CLASS_", TRUE);
    class SocialNetWorks {

        var $error = false;
        var $errorMsg = array();
        var $networks = array();
        var $adnbp = null;

        function SocialNetWorks() {
            global $adnbp;
            $this->adnbp = &$adnbp;
            $this->networks =
                ['google'=>['available'=>$this->adnbp->getConf('GoogleOauth') && strlen($this->adnbp->getConf('GoogleOauth_CLIENT_ID')) && strlen($this->adnbp->getConf('GoogleOauth_CLIENT_SECRET'))
                    ,'active'=>$this->adnbp->getConf('GoogleOauth')
                    ,'client_id'=>(strlen($this->adnbp->getConf('GoogleOauth_CLIENT_ID')))?$this->adnbp->getConf('GoogleOauth_CLIENT_ID'):null
                    ,'client_secret'=>(strlen($this->adnbp->getConf('GoogleOauth_CLIENT_SECRET')))?$this->adnbp->getConf('GoogleOauth_CLIENT_SECRET'):null
                    ,'client_scope'=>(is_array($this->adnbp->getConf('GoogleOauth_SCOPE'))) && (count($this->adnbp->getConf('GoogleOauth_SCOPE')) > 0)?$this->adnbp->getConf('GoogleOauth_SCOPE'):null
                ],
                    'facebook'=>['available'=>$this->adnbp->getConf('FacebookOauth') && strlen($this->adnbp->getConf('FacebookOauth_APP_ID')) && strlen($this->adnbp->getConf('FacebookOauth_APP_SECRET'))
                        ,'active'=>$this->adnbp->getConf('GoogleOauth')
                        ,'client_id'=>(strlen($this->adnbp->getConf('FacebookOauth_CLIENT_ID')))?$this->adnbp->getConf('FacebookOauth_CLIENT_ID'):null
                        ,'client_secret'=>(strlen($this->adnbp->getConf('FacebookOauth_CLIENT_ID')))?$this->adnbp->getConf('FacebookOauth_CLIENT_SECRET'):null
                        ,'client_scope'=>(is_array($this->adnbp->getConf('FacebookOauth_SCOPE'))) && (count($this->adnbp->getConf('FacebookOauth_SCOPE')) > 0)?$this->adnbp->getConf('FacebookOauth_SCOPE'):null
                    ],
                    'tnstagram'=>['available'=>$this->adnbp->getConf('InstagramOauth') && strlen($this->adnbp->getConf('InstagramOauth_CLIENT_ID')) && strlen($this->adnbp->getConf('InstagramOauth_CLIENT_SECRET'))
                        ,'active'=>$this->adnbp->getConf('InstagramOauth')
                        ,'client_id'=>(strlen($this->adnbp->getConf('InstagramOauth_CLIENT_ID')))?$this->adnbp->getConf('InstagramOauth_CLIENT_ID'):null
                        ,'client_secret'=>(strlen($this->adnbp->getConf('InstagramOauth_CLIENT_SECRET')))?$this->adnbp->getConf('InstagramOauth_CLIENT_SECRET'):null
                        ,'client_scope'=>(is_array($this->adnbp->getConf('InstagramOauth_SCOPE'))) && (count($this->adnbp->getConf('InstagramOauth_SCOPE')) > 0)?$this->adnbp->getConf('InstagramOauth_SCOPE'):null
                    ],
                    'twitter'=>['available'=>$this->adnbp->getConf('TwitterOauth') && strlen($this->adnbp->getConf('TwitterOauth_KEY')) && strlen($this->adnbp->getConf('TwitterOauth_SECRET'))
                        ,'active'=>$this->adnbp->getConf('TwitterOauth')
                        ,'client_id'=>(strlen($this->adnbp->getConf('TwitterOauth_KEY')))?"****":"missing"
                        ,'client_secret'=>(strlen($this->adnbp->getConf('TwitterOauth_SECRET')))?"****":"missing"
                    ],
                    'vkontakte'=>['available'=>$this->adnbp->getConf('VKontakteOauth') && strlen($this->adnbp->getConf('VKontakteOauth_APP_ID')) && strlen($this->adnbp->getConf('VKontakteOauth_APP_ID'))
                        ,'active'=>$this->adnbp->getConf('VKontakteOauth')
                        ,'client_id'=>(strlen($this->adnbp->getConf('VKontakteOauth_APP_ID')))?"****":"missing"
                        ,'client_secret'=>(strlen($this->adnbp->getConf('VKontakteOauth_APP_SECRET')))?"****":"missing"
                    ]
                ];
        }

        function isAvailable($sc) {
            return (isset($this->networks[strtolower($sc)]) && $this->networks[strtolower($sc)]['available']);
        }

        function setError($msg) {
            $this->errorMsg = array();
            $this->addError($msg);
        }
        function addError($msg) {
            $this->error = true;
            $this->errorMsg[] = $msg;
        }
    }
}
