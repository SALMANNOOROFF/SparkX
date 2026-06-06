<?php
// gateways/GatewayInterface.php

interface GatewayInterface {
    public function initiatePayment($data);
    public function validatePayment($data);
}
