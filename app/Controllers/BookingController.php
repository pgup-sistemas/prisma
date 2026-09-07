<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\HubBooking;

class BookingController extends Controller
{
    private const STATUSES = ['pending', 'confirmed', 'cancelled'];

    public function index(): void
    {
        $this->requireAuth();

        $status = (string) $this->request->input('status', '');
        if (!in_array($status, self::STATUSES, true)) {
            $status = '';
        }

        $bookings = HubBooking::forUser((int) $this->user()['id'], $status);

        $this->render('agenda/index', [
            'title'    => 'Agenda',
            'bookings' => $bookings,
            'status'   => $status,
        ]);
    }

    public function confirm(string $id): void
    {
        $this->requireAuth();
        $this->verifyCsrf();

        $booking = $this->ownedBooking((int) $id);
        HubBooking::update((int) $booking['id'], ['status' => 'confirmed']);

        $this->json(['success' => true]);
    }

    public function cancel(string $id): void
    {
        $this->requireAuth();
        $this->verifyCsrf();

        $booking = $this->ownedBooking((int) $id);
        HubBooking::update((int) $booking['id'], ['status' => 'cancelled']);

        $this->json(['success' => true]);
    }

    private function ownedBooking(int $id): array
    {
        $booking = HubBooking::ownedByUser($id, (int) $this->user()['id']);
        if ($booking === null) {
            $this->json(['success' => false, 'error' => 'Agendamento não encontrado.'], 404);
            exit;
        }
        return $booking;
    }
}
