<?php

namespace Plugin\EccubeApi\Repository\OAuth2;

use Doctrine\ORM\EntityRepository;
use Plugin\EccubeApi\Entity\OAuth2\AccessToken;
use OAuth2\Storage\AccessTokenInterface;


/**
 * AccessTokenRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 *
 * @author Kentaro Ohkouchi
 * @link http://bshaffer.github.io/oauth2-server-php-docs/cookbook/doctrine2/
 */
class AccessTokenRepository extends EntityRepository implements AccessTokenInterface
{
    /**
     * トークンを指定して、 access token のフィールドの配列を返す.
     *
     * @param string $oauthToken トークン文字列
     * @return array access token のフィールドの配列
     */
    public function getAccessToken($oauthToken)
    {
        $AccessToken = $this->findOneBy(array('token' => $oauthToken));
        if (is_object($AccessToken)) {
            $token = $AccessToken->toArray();
            $token['client_id'] = $AccessToken->getClient()->getId();
            $token['expires'] = $token['expires']->getTimestamp();
            return $token;
        }
        return null;
    }

    /**
     * AccessToken を生成して保存する.
     *
     * @param string $oauthToken トークン文字列
     * @param string $clientIdentifier client_id 文字列
     * @param integer $user_id UserInfo::id
     * @param integer $expires 有効期限の UNIX タイムスタンプ
     * @param string $scope 認可された scope. スペース区切りで複数指定可能
     * @return void
     */
    public function setAccessToken($oauthToken, $clientIdentifier, $user_id, $expires, $scope = null)
    {
        $client = $this->_em->getRepository('Plugin\EccubeApi\Entity\OAuth2\Client')
                            ->findOneBy(array('client_identifier' => $clientIdentifier));
        // UserInfo::sub ではなく UserInfo::id が渡ってくることに注意
        $user = $this->_em->getRepository('Plugin\EccubeApi\Entity\OAuth2\OpenID\UserInfo')->find($user_id);
        $AccessToken = new \Plugin\EccubeApi\Entity\OAuth2\AccessToken();
        $now = new \DateTime();
        $AccessToken->setPropertiesFromArray(array(
            'token'     => $oauthToken,
            'client'    => $client,
            'user'      => $user,
            'expires'   => $now->setTimestamp($expires),
            'scope'     => $scope,
        ));
        $this->_em->persist($AccessToken);
        $this->_em->flush();
    }
}
