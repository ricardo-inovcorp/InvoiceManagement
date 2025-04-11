<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NifService
{
    protected $apiUrl = 'https://www.nif.pt';
    protected $apiKey;

    public function __construct()
    {
        $this->apiKey = env('NIF_PT_API_KEY');
    }

    /**
     * Consulta informações de uma empresa pelo NIF
     *
     * @param string $nif
     * @return array|null
     */
    public function getCompanyByNif(string $nif)
    {
        try {
            $url = "{$this->apiUrl}/?json=1&q={$nif}&key={$this->apiKey}";
            Log::info("Consultando NIF URL: {$url}");
            
            $response = Http::get($url);
            
            if ($response->successful()) {
                $responseBody = $response->body();
                Log::info("Resposta da API NIF: " . $responseBody);
                $data = $response->json();
                
                // Verificar e logar a estrutura completa da resposta
                Log::info("Estrutura da resposta: " . json_encode($data));
                
                // Examinando a estrutura de dados 
                if (isset($data['records'])) {
                    Log::info("Records encontrado: " . json_encode($data['records']));
                    Log::info("Tipo de records: " . gettype($data['records']));
                    if (is_array($data['records'])) {
                        Log::info("Tamanho do array records: " . count($data['records']));
                    }
                } else {
                    Log::info("Records não encontrado na resposta");
                    // Verificar outras possíveis estruturas
                    Log::info("Chaves na resposta: " . json_encode(array_keys($data)));
                }
                
                // Extrair os dados da resposta de acordo com a estrutura
                $companyData = $this->extractCompanyData($data);
                if ($companyData) {
                    return [
                        'success' => true,
                        'data' => $companyData
                    ];
                }
                
                return [
                    'success' => false,
                    'message' => 'NIF não encontrado ou com formato inválido'
                ];
            }
            
            Log::error("Erro ao consultar API NIF. Status: " . $response->status());
            return [
                'success' => false,
                'message' => 'Erro ao consultar a API do NIF.pt. Status: ' . $response->status()
            ];
        } catch (\Exception $e) {
            Log::error('Erro ao consultar NIF: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Erro ao consultar NIF: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Extrai dados da empresa de diferentes formatos de resposta da API
     *
     * @param array $data
     * @return array|null
     */
    private function extractCompanyData(array $data): ?array
    {
        try {
            // Formato 1: Resposta com records
            if (isset($data['result']) && $data['result'] === 'success' && 
                isset($data['records']) && is_array($data['records'])) {
                
                // Tratamento especial para diferentes formatos de array records
                if (!empty($data['records'])) {
                    $record = null;
                    
                    // Verificar se é um array indexado ou associativo
                    if (array_key_exists(0, $data['records'])) {
                        $record = $data['records'][0];
                    } else {
                        // Pegar o primeiro item do array, independente da chave
                        $keys = array_keys($data['records']);
                        if (!empty($keys)) {
                            $firstKey = reset($keys);
                            $record = $data['records'][$firstKey];
                        }
                    }
                    
                    if ($record) {
                        Log::info("Registro encontrado: " . json_encode($record));
                        
                        // Extrair dados com segurança, tratando possíveis estruturas diferentes
                        $contacts = isset($record['contacts']) && is_array($record['contacts']) ? $record['contacts'] : [];
                        $phone = null;
                        $email = null;
                        $website = null;
                        
                        // Verificar diferentes formatos possíveis para contatos
                        if (is_array($contacts)) {
                            $phone = $contacts['phone'] ?? null;
                            $email = $contacts['email'] ?? null;
                            $website = $contacts['website'] ?? null;
                        }
                        
                        // Garantir formato correto do código postal
                        $zipCode = $record['pc'] ?? null;
                        if ($zipCode) {
                            $zipCode = $this->formatZipCode($zipCode);
                        }
                        
                        return [
                            'company_name' => $record['title'] ?? null,
                            'address' => $record['address'] ?? null,
                            'zip_code' => $zipCode,
                            'city' => $record['locale'] ?? null,
                            'district' => $record['district'] ?? null,
                            'county' => $record['county'] ?? null,
                            'phone' => $phone,
                            'email' => $email,
                            'website' => $website,
                            'cae' => $record['cae'] ?? null,
                            'cae_description' => $record['activity'] ?? null,
                        ];
                    }
                }
            }
            
            // Formato 2: Resposta direta sem records
            if (isset($data['name']) || isset($data['title']) || isset($data['result'])) {
                $zipCode = $data['postalCode'] ?? $data['postal_code'] ?? $data['zip_code'] ?? $data['pc'] ?? null;
                if ($zipCode) {
                    $zipCode = $this->formatZipCode($zipCode);
                }
                
                return [
                    'company_name' => $data['name'] ?? $data['title'] ?? null,
                    'address' => $data['address'] ?? null,
                    'zip_code' => $zipCode,
                    'city' => $data['city'] ?? $data['locale'] ?? null,
                    'district' => $data['district'] ?? null,
                    'county' => $data['county'] ?? null,
                    'phone' => $data['phone'] ?? $data['telephone'] ?? null,
                    'email' => $data['email'] ?? null,
                    'website' => $data['website'] ?? null,
                    'cae' => $data['cae'] ?? null,
                    'cae_description' => $data['activity'] ?? $data['caeDescription'] ?? null,
                ];
            }
            
            // Formato 3: Resposta em um formato completamente diferente
            // Aqui tentamos extrair informações úteis de qualquer formato
            if (is_array($data)) {
                $companyData = [];
                
                // Buscar possíveis chaves que contenham dados relevantes
                foreach ($data as $key => $value) {
                    if (is_string($value)) {
                        // Tenta identificar o tipo de dado com base no nome da chave
                        if (preg_match('/(name|empresa|title|nome)/i', $key)) {
                            $companyData['company_name'] = $value;
                        } elseif (preg_match('/(address|morada|endereco)/i', $key)) {
                            $companyData['address'] = $value;
                        } elseif (preg_match('/(city|cidade|localidade|locale)/i', $key)) {
                            $companyData['city'] = $value;
                        } elseif (preg_match('/(district|distrito)/i', $key)) {
                            $companyData['district'] = $value;
                        } elseif (preg_match('/(county|concelho)/i', $key)) {
                            $companyData['county'] = $value;
                        } elseif (preg_match('/(postal|zip|cep|codigo)/i', $key)) {
                            $companyData['zip_code'] = $this->formatZipCode($value);
                        } elseif (preg_match('/(phone|telefone|tel)/i', $key)) {
                            $companyData['phone'] = $value;
                        } elseif (preg_match('/(email|e-mail)/i', $key)) {
                            $companyData['email'] = $value;
                        }
                    }
                }
                
                // Se encontramos pelo menos o nome da empresa, retornamos os dados
                if (!empty($companyData) && isset($companyData['company_name'])) {
                    return $companyData;
                }
            }
            
            return null;
        } catch (\Exception $e) {
            Log::error('Erro ao extrair dados da empresa: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Formata o código postal para o formato correto (0000-000)
     *
     * @param string $zipCode
     * @return string
     */
    private function formatZipCode(string $zipCode): string
    {
        // Remover tudo que não for dígito
        $zipCode = preg_replace('/[^0-9]/', '', $zipCode);
        
        // Se tiver pelo menos 4 dígitos, formatar como 0000-000
        if (strlen($zipCode) >= 4) {
            return substr($zipCode, 0, 4) . '-' . 
                  (strlen($zipCode) > 4 ? substr($zipCode, 4, 3) : '000');
        }
        
        return $zipCode;
    }
} 