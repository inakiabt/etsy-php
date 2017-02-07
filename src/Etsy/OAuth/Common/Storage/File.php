<?php
namespace Etsy\OAuth\Common\Storage;

use OAuth\Common\Storage\Exception\AuthorizationStateNotFoundException;
use OAuth\Common\Storage\Exception\StorageException;
use OAuth\Common\Storage\Exception\TokenNotFoundException;
use OAuth\Common\Storage\TokenStorageInterface;
use OAuth\Common\Token\TokenInterface;

class File implements TokenStorageInterface {

    /**
     * @var array of object|TokenInterface
     */
    protected $tokens;
    /**
     * @var array
     */
    protected $states;
    /**
     * @var string
     */
    protected $filePath;

    public function __construct($filePath)
    {
        $this->tokens = array();
        $this->states = array();

        if (!is_writeable(dirname($filePath))) {
            throw new StorageException('File "' . $filePath. '" is not writeable');
        }

        $this->filePath = $filePath;

        if (!file_exists($filePath)) {
            $data = array('tokens' => array(), 'states' => array());
        } else {
            $data = unserialize(file_get_contents($this->filePath));
        }

        if ($data === false) {
            throw new StorageException('File "' . $filePath. '" is invalid');
        }

        $this->tokens = $data['tokens'];
        $this->states = $data['states'];
    }

    /**
     * {@inheritDoc}
     */
    public function retrieveAccessToken($service)
    {
        if ($this->hasAccessToken($service)) {
            return $this->tokens[$service];
        }

        throw new TokenNotFoundException('Token not stored');
    }

    /**
     * {@inheritDoc}
     */
    public function storeAccessToken($service, TokenInterface $token)
    {
        $this->tokens[$service] = $token;
        $this->updateFile();

        // allow chaining
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function hasAccessToken($service)
    {
        return isset($this->tokens[$service]) && $this->tokens[$service] instanceof TokenInterface;
    }

    /**
     * {@inheritDoc}
     */
    public function clearToken($service)
    {
        if (array_key_exists($service, $this->tokens)) {
            unset($this->tokens[$service]);
            $this->updateFile();
        }

        // allow chaining
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function clearAllTokens()
    {
        $this->tokens = array();
        $this->updateFile();

        // allow chaining
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function retrieveAuthorizationState($service)
    {
        if ($this->hasAuthorizationState($service)) {
            return $this->states[$service];
        }
        throw new AuthorizationStateNotFoundException('State not stored');
    }

    /**
     * {@inheritDoc}
     */
    public function storeAuthorizationState($service, $state)
    {
        $this->states[$service] = $state;
        $this->updateFile();

        // allow chaining
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function hasAuthorizationState($service)
    {
        return isset($this->states[$service]) && null !== $this->states[$service];
    }

    /**
     * {@inheritDoc}
     */
    public function clearAuthorizationState($service)
    {
        if (array_key_exists($service, $this->states)) {
            unset($this->states[$service]);
            $this->updateFile();
        }

        // allow chaining
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function clearAllAuthorizationStates()
    {
        $this->states = array();
        $this->updateFile();

        // allow chaining
        return $this;
    }

    /**
     * Update tokens and states
     */
    private function updateFile()
    {
        file_put_contents($this->filePath, serialize(array(
            'tokens' => $this->tokens,
            'states' => $this->states
        )));
    }
}
