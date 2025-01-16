<?php

class HyperionControl extends IPSModule
{
    /**
     * Instanz initialisieren.
     */
    public function Create()
    {
        // Diese Zeile nicht löschen
        parent::Create();

        // Konfigurationsparameter definieren
        $this->RegisterPropertyString('Host', '127.0.0.1');
        $this->RegisterPropertyInteger('Port', 19444);
        $this->RegisterPropertyString('AuthToken', '');
    }

    /**
     * Einstellungen beim Aktivieren prüfen.
     */
    public function ApplyChanges()
    {
        // Diese Zeile nicht löschen
        parent::ApplyChanges();
    }

    /**
     * Sende einen Befehl an den Hyperion-Server.
     *
     * @param string $command Der Befehl, z. B. "serverinfo" oder "componentstate".
     * @param array $params Zusätzliche Parameter für den Befehl.
     * @return mixed Die Antwort des Hyperion-Servers.
     */
    public function SendCommand(string $command, array $params = [])
    {
        $host = $this->ReadPropertyString('Host');
        $port = $this->ReadPropertyInteger('Port');
        $authToken = $this->ReadPropertyString('AuthToken');

        $url = "http://{$host}:{$port}/json-rpc";
        $data = json_encode(array_merge(['command' => $command], $params));

        $ch = curl_init($url);

        $headers = [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data),
        ];

        if (!empty($authToken)) {
            $headers[] = 'Authorization: token ' . $authToken;
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            $this->LogMessage('cURL Error: ' . curl_error($ch), KL_ERROR);
            $response = null;
        }

        curl_close($ch);

        return $response;
    }

    /**
     * Hyperion ausschalten.
     */
    public function TurnOff()
    {
        $command = 'componentstate';
        $params = [
            'componentstate' => [
                'component' => 'ALL',
                'state' => false,
            ],
        ];

        $response = $this->SendCommand($command, $params);
        $this->SendDebug('TurnOff Response', $response, 0);
    }

    /**
     * Hyperion einschalten.
     */
    public function TurnOn()
    {
        $command = 'componentstate';
        $params = [
            'componentstate' => [
                'component' => 'ALL',
                'state' => true,
            ],
        ];

        $response = $this->SendCommand($command, $params);
        $this->SendDebug('TurnOn Response', $response, 0);
    }

    /**
     * Serverinformationen abrufen.
     *
     * @return string Serverinformationen als JSON.
     */
    public function GetServerInfo()
    {
        $command = 'serverinfo';
        $response = $this->SendCommand($command);
        $this->SendDebug('ServerInfo Response', $response, 0);
        return $response;
    }
}