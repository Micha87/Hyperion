<?php

declare(strict_types=1);
	class HyperionControl extends IPSModule
	{
		
    public function Create()
		{
        // Diese Zeile nicht löschen
        parent::Create();

        // Konfigurationsparameter definieren
        $this->RegisterPropertyString('IPAddress', '');
	$this->RegisterPropertyString('Port', '');
        $this->RegisterPropertyString('Token', '');
    }
		
    public function Destroy()
	{
			//Never delete this line!
			parent::Destroy();
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
        $host = $this->ReadPropertyString('IPAddress');
        $port = $this->ReadPropertyString('Port');
        $authToken = $this->ReadPropertyString('Token');

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

/**
     * Ruft die Liste der Instanzen ab.
     *
     * @return array|null Die Liste der Instanzen oder null bei Fehlern.
     */
    public function GetInstances()
    {
        $command = 'serverinfo';
        $response = $this->SendCommand($command);

        if ($response) {
            $decodedResponse = json_decode($response, true);

            if (isset($decodedResponse['info']['instance'])) {
                $instances = $decodedResponse['info']['instance'];
                $this->SendDebug('Instance', json_encode($instances), 0);
                return $instances;
            }
        }

        $this->LogMessage('Fehler beim Abrufen der Instanzen', KL_ERROR);
        return null;
    }

	public function StartInstance(int $instanceId)
    {
        $command = 'instance';
        $params = [
            'instance' => $instanceId,
            'action' => 'start'
        ];

        $response = $this->SendCommand($command, $params);
        $this->SendDebug("Start Instance {$instanceId}", $response, 0);
    }

    /**
     * Stoppt eine spezifische Instanz.
     *
     * @param int $instanceId Die ID der Instanz, die gestoppt werden soll.
     */
    public function StopInstance(int $instanceId)
    {
        $command = 'instance';
        $params = [
            'instance' => $instanceId,
            'action' => 'stop'
        ];

        $response = $this->SendCommand($command, $params);
        $this->SendDebug("Stop Instance {$instanceId}", $response, 0);
    }

     public function StopInstance1(int $instanceId)
    {
        //$command = 'instance';
        $command = [
         	"command" => "instance",
  		"subcommand" => "startInstance",
  		"instance" => $instanceId;
        ];

        $response = $this->SendCommand($command);
        $this->SendDebug("Stop Instance {$instanceId}", $response, 0);
    }

		
}
