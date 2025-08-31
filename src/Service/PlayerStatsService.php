<?php

namespace App\Service;

use App\Entity\Team;
use App\Entity\Player;
use App\Repository\GoalRepository;
use App\Repository\EventRepository;
use App\Repository\PlayerRepository;
use App\Repository\PresenceRepository;
use App\Repository\ConvocationRepository;


class PlayerStatsService
{
    private EventRepository $eventRepository; 
    private ConvocationRepository $convocationRepository;
    private PresenceRepository $presenceRepository;

    public function __construct(
        EventRepository $eventRepository,
        ConvocationRepository $convocationRepository,
        PresenceRepository $presenceRepository
    ) {
        $this->eventRepository = $eventRepository;
        $this->convocationRepository = $convocationRepository;
        $this->presenceRepository = $presenceRepository;
    }

    public function getPlayerStats(Team $team, Player $player): array
    {
        $totalMatches = $this->eventRepository->countMatchesByTeam($team);
        $totalTrainingSessions = $this->eventRepository->countTrainingSessionsByTeam($team);
        $totalConvocations = $this->convocationRepository->countConvocatedMatchesByPlayer($player);
        $totalMatchesPresences = $this->presenceRepository->countMatchesPresencesByPlayer($player);
        $totalTrainingSessionsPresences = $this->presenceRepository->countTrainingSessionsPresencesByPlayer($player);

        $convocationRate = $totalMatches > 0 
            ? ($totalConvocations / $totalMatches) * 100 
            : 0;
        $trainingPresenceRate = $totalTrainingSessions > 0 
            ? ($totalTrainingSessionsPresences / $totalTrainingSessions) * 100 
            : 0;
        $matchPresenceRate = $totalConvocations > 0 
            ? ($totalMatchesPresences / $totalConvocations) * 100 
            : 0;

        return [
            'totalMatches' => $totalMatches,
            'totalTrainingSessions' => $totalTrainingSessions,
            'totalConvocations' => $totalConvocations,
            'totalMatchesPresences' => $totalMatchesPresences,
            'totalTrainingSessionsPresences' => $totalTrainingSessionsPresences,
            'convocationRate' => round($convocationRate),
            'trainingPresenceRate' => round($trainingPresenceRate, 2),
            'matchPresenceRate' => round($matchPresenceRate, 2)
        ];
    }

}