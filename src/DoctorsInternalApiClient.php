<?php

declare(strict_types=1);

namespace App;

use App\VO\DoctorsApiConnectionConfig;
use App\VO\DoctorVO;
use App\VO\SlotVO;
use DateMalformedStringException;
use DateTimeImmutable;
use Generator;
use LogicException;
use Psr\Log\LoggerInterface;
use App\Shared\RequestMode;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use JsonException;
use Throwable;

readonly class DoctorsInternalApiClient implements DoctorsApiClientInterface
{
    private const DOCTORS_API_URI = '/api/doctors';
    private const DOCTOR_SLOTS_BULK_API_URI = '/api/doctors/slots';

    public function __construct(
        private DoctorsApiConnectionConfigFactory $apiConnectionConfigFactory,
        private HttpClientInterface $httpClient,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * @throws TransportExceptionInterface
     * @throws JsonException
     */
    public function getAllDoctors(): Generator
    {
        try {
            $data = $this->fetchData(self::DOCTORS_API_URI);
            $doctors = json_decode($data, true, 512, JSON_THROW_ON_ERROR);
            $doctors = $doctors['data'] ?? [];

            foreach ($doctors as $doctor) {
                $id = $doctor['id'] ?? null;
                $name = $doctor['name'] ?? null;
                if (null === $id || null === $name) {
                    $this->logger->warning('Invalid response structure received, required doctor: id or name.', [
                        'doctor' => $doctor,
                    ]);
                    continue;
                }
                yield new DoctorVO((int) $id, $name);
            }
        } catch (JsonException $e) {
            $this->logger->error('Error decoding doctors JSON', ['error' => $e->getMessage()]);
            throw $e;
        } catch (Throwable $e) {
            $this->logger->error(sprintf('Unexpected error occurred: %s', $e->getMessage()), ['error' => $e]);
            throw $e;
        }
    }

    /**
     * @param int[] $doctorIds
     *
     * @throws TransportExceptionInterface
     * @throws JsonException
     */
    public function getSlotsByDoctors(Generator $doctors): Generator
    {
        $doctorsByIds = [];
        $doctorIds = [];

        /** @var DoctorVO $doctorItem */
        foreach ($doctors as $doctorItem) {
            $doctorId = $doctorItem->getId();
            $doctorsByIds[$doctorId] = $doctorItem;
            $doctorIds[] = $doctorId;
        }

        try {
            $data = $this->bulkFetchDoctorsSlotsData($doctorIds);
            $response = json_decode($data, true, 512, JSON_THROW_ON_ERROR);
            $response = $response['data'] ?? [];

            foreach ($response as $doctorId => $slotsByDoctor) {
                $slots = [];
                $doctor = $doctorsByIds[$doctorId] ?? null;

                if (null === $doctor) {
                    throw new LogicException('Doctor not found in related map.');
                }

                foreach ($slotsByDoctor as $slot) {
                    $id = $slot['id'] ?? null;
                    $start = $slot['start']['date'] ?? null;
                    $end = $slot['end']['date'] ?? null;

                    if (null === $id || null === $start || null === $end) {
                        $this->logger->error('Invalid response structure received.', [
                            'slot' => $slot,
                        ]);
                        continue;
                    }

                    try {
                        $slots[] = new SlotVO(
                            $id,
                            $doctor,
                            new DateTimeImmutable($start),
                            new DateTimeImmutable($end)
                        );
                    } catch (DateMalformedStringException $e) {
                        $this->logger->error('Date malformed in doctor slots data.', [
                            'start' => $start,
                            'end' => $end,
                            'exception' => $e->getMessage(),
                        ]);
                    }
                }

                yield $doctorId => $slots;
            }
        } catch (JsonException $e) {
            $this->logger->error('Error decoding bulk doctors slots JSON', ['error' => $e->getMessage()]);
            throw $e;
        } catch (Throwable $e) {
            $this->logger->error(sprintf('Unexpected error occurred: %s', $e->getMessage()), ['error' => $e]);
            throw $e;
        }
    }

    private function bulkFetchDoctorsSlotsData(array $doctorIds): string
    {
        try {
            $url = sprintf(
                '%s/%s',
                $this->getConfig()->getBaseUri(),
                ltrim(self::DOCTOR_SLOTS_BULK_API_URI, '/')
            );

            $response = $this->httpClient->request('POST', $url, [
                'headers' => [
                    'Authorization' => 'Basic ' . $this->getConfig()->getAuthValue(),
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'mode' => RequestMode::READ,
                    'doctorIds' => $doctorIds,
                ],
            ]);

            return $this->getResponseContent($response);
        } catch (HttpExceptionInterface $e) {
            $this->logger->error('Error fetching bulk data from API', [
                'error' => $e->getMessage(),
                'doctorIds' => $doctorIds,
            ]);
            throw $e;
        }
    }

    private function fetchData(string $uri): string
    {
        try {
            $url = sprintf('%s/%s', $this->getConfig()->getBaseUri(), ltrim($uri, '/'));
            $response = $this->httpClient->request('GET', $url, [
                'headers' => [
                    'Authorization' => 'Basic ' . $this->getConfig()->getAuthValue(),
                ],
            ]);

            return $this->getResponseContent($response);
        } catch (HttpExceptionInterface $e) {
            $this->logger->error('Error fetching data from API', [
                'error' => $e->getMessage(),
                'uri' => $uri,
            ]);
            throw $e;
        }
    }

    private function getResponseContent(ResponseInterface $response): string
    {
        try {
            return $response->getContent();
        } catch (HttpExceptionInterface $e) {
            $this->logger->error('Error getting response content', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    private function getConfig(): DoctorsApiConnectionConfig
    {
        return $this->apiConnectionConfigFactory->getConfig();
    }
}
