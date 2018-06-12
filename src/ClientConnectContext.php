<?php

namespace Amp\Socket;

use Amp\Dns\Record;
use function Amp\Socket\Internal\normalizeBindToOption;

final class ClientConnectContext {
    private $bindTo = null;
    private $connectTimeout = 10000;
    private $maxAttempts = 2;
    private $typeRestriction = null;
    private $tcpNoDelay = false;
    private $uriConcurrencyLimit = [];
    private $defaultUriConcurrencyLimit = null;

    public function withUriConcurrencyLimit(string $uri, int $maxConnections): self {
        if ($maxConnections <= 0) {
            throw new \Error("Invalid URI concurrency limit ({$maxConnections}), must be greater than 0");
        }

        $clone = clone $this;
        $clone->uriConcurrencyLimit[$uri] = $maxConnections;

        return $clone;
    }

    public function getUriConcurrencyLimit(string $uri, bool $useDefault = false) {
        if (isset($this->uriConcurrencyLimit[$uri])) {
            return $this->uriConcurrencyLimit[$uri];
        }

        return $useDefault
            ? $this->defaultUriConcurrencyLimit
            : null;
    }

    public function withDefaultUriConcurrencyLimit(int $maxConnections): self {
        if ($maxConnections <= 0) {
            throw new \Error("Invalid URI concurrency limit ({$maxConnections}), must be greater than 0");
        }

        $clone = clone $this;
        $clone->defaultUriConcurrencyLimit = $maxConnections;

        return $clone;
    }

    public function getDefaultUriConcurrencyLimit(string $uri) {
        return $this->bindTo;
    }

    public function withBindTo(string $bindTo = null): self {
        $bindTo = normalizeBindToOption($bindTo);

        $clone = clone $this;
        $clone->bindTo = $bindTo;

        return $clone;
    }

    public function getBindTo() {
        return $this->bindTo;
    }

    public function withConnectTimeout(int $timeout): self {
        if ($timeout <= 0) {
            throw new \Error("Invalid connect timeout ({$timeout}), must be greater than 0");
        }

        $clone = clone $this;
        $clone->connectTimeout = $timeout;

        return $clone;
    }

    public function getConnectTimeout(): int {
        return $this->connectTimeout;
    }

    public function withMaxAttempts(int $maxAttempts): self {
        if ($maxAttempts <= 0) {
            throw new \Error("Invalid max attempts ({$maxAttempts}), must be greater than 0");
        }

        $clone = clone $this;
        $clone->maxAttempts = $maxAttempts;

        return $clone;
    }

    public function getMaxAttempts(): int {
        return $this->maxAttempts;
    }

    public function withDnsTypeRestriction(int $type = null): self {
        if ($type !== null && $type !== Record::AAAA && $type !== Record::A) {
            throw new \Error("Invalid resolver type restriction");
        }

        $clone = clone $this;
        $clone->typeRestriction = $type;

        return $clone;
    }

    public function getDnsTypeRestriction() {
        return $this->typeRestriction;
    }

    public function hasTcpNoDelay(): bool {
        return $this->tcpNoDelay;
    }

    public function withTcpNoDelay(): self {
        $clone = clone $this;
        $clone->tcpNoDelay = true;

        return $clone;
    }

    public function withoutTcpNoDelay(): self {
        $clone = clone $this;
        $clone->tcpNoDelay = false;

        return $clone;
    }

    public function toStreamContextArray(): array {
        $options = [
            "tcp_nodelay" => $this->tcpNoDelay,
        ];

        if ($this->bindTo !== null) {
            $options["bindto"] = $this->bindTo;
        }

        return ["socket" => $options];
    }
}
