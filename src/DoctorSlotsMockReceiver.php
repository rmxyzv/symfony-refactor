<?php

declare(strict_types=1);

namespace App;

use DateInterval;
use DateMalformedIntervalStringException;
use DateMalformedStringException;
use DateTimeImmutable;
use InvalidArgumentException;
use Random\RandomException;

final readonly class DoctorSlotsMockReceiver
{
    public static function getDoctors(): array
    {
        return [
            [
                "id" => 0,
                "name" => "Adoring Shtern"
            ],
            [
                "id" => 1,
                "name" => "Brave Ramanujan"
            ],
            [
                "id" => 2,
                "name" => "Tender Rosalind"
            ],
            [
                "id" => 3,
                "name" => "Beautiful Stonebraker"
            ],
            [
                "id" => 4,
                "name" => "Brave Northcutt"
            ],
            [
                "id" => 5,
                "name" => "Loving Shaw"
            ],
            [
                "id" => 6,
                "name" => "Clever Noyce"
            ],
            [
                "id" => 7,
                "name" => "Hopeful Hopper"
            ],
            [
                "id" => 8,
                "name" => "Boring Curran"
            ],
            [
                "id" => 9,
                "name" => "Vigorous Rhodes"
            ],
            [
                "id" => 10,
                "name" => "Lucid Lehmann"
            ],
            [
                "id" => 11,
                "name" => "Magical Leakey"
            ],
            [
                "id" => 12,
                "name" => "Kind Davinci"
            ],
            [
                "id" => 13,
                "name" => "Friendly Swirles"
            ],
            [
                "id" => 14,
                "name" => "Elastic Pare"
            ],
            [
                "id" => 15,
                "name" => "Kind Bhabha"
            ],
            [
                "id" => 16,
                "name" => "Confident Beaver"
            ],
            [
                "id" => 17,
                "name" => "Gracious Solomon"
            ],
            [
                "id" => 18,
                "name" => "Funny Hopper"
            ],
            [
                "id" => 19,
                "name" => "Sweet Meninsky"
            ],
            [
                "id" => 20,
                "name" => "Exciting Bartik"
            ],
            [
                "id" => 21,
                "name" => "Boring Sutherland"
            ],
            [
                "id" => 22,
                "name" => "Vibrant Jepsen"
            ],
            [
                "id" => 23,
                "name" => "Wizardly Dhawan"
            ],
            [
                "id" => 24,
                "name" => "Infallible Shamir"
            ],
            [
                "id" => 25,
                "name" => "Dazzling Meitner"
            ],
            [
                "id" => 26,
                "name" => "Elated Payne"
            ],
            [
                "id" => 27,
                "name" => "Festive Villani"
            ],
            [
                "id" => 28,
                "name" => "Affectionate Yonath"
            ],
            [
                "id" => 29,
                "name" => "Eloquent Brown"
            ]
        ];
    }

    /**
     * @param array<int> $doctorIds
     * @return array<int, array<int, array{id: int, start: DateTimeImmutable, end: DateTimeImmutable}>>
     *
     * @throws DateMalformedIntervalStringException
     * @throws DateMalformedStringException
     * @throws RandomException
     */
    public static function getSlotsByDoctors(array $doctorIds = []): array
    {
        if ($doctorIds === []) {
            throw new InvalidArgumentException('$doctorIds can not be empty');
        }
        $doctors = self::getDoctors();
        $doctors[] = [
            "id" => 30,
            "name" => "John Doe"
        ];
        $slotsByDoctor = [];
        $slotId = 0;

        foreach ($doctors as $doctor) {
            if (!in_array($doctor['id'], $doctorIds, true)) {
                continue;
            }
            $numberOfSlots = random_int(1, 5);

            for ($i = 0; $i < $numberOfSlots; $i++) {
                $startHour = random_int(8, 16);
                $startMinute = random_int(0, 59);
                $start = new DateTimeImmutable('2025-10-' . random_int(1, 28) . " {$startHour}:{$startMinute}");

                // random slot duration
                $durationMinutes = [30, 60, 90];
                $duration = $durationMinutes[array_rand($durationMinutes)];
                $end = $start->add(new DateInterval('PT' . $duration . 'M'));

                $slotsByDoctor[$doctor['id']][] = [
                    'id' => ++$slotId,
                    'start' => $start,
                    'end' => $end
                ];
            }
        }

        return $slotsByDoctor;
    }
}
