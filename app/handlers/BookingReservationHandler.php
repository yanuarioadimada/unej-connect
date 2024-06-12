<?php

class BookingReservationHandler extends BookingReservationDAO
{
    private $reservation;
    private $pricing;
    private $executionFeedback;

    public function __construct(Reservation $reservation, Pricing $pricing)
    {
        $this->reservation = $reservation;
        $this->pricing = $pricing;
    }

    public function getExecutionFeedback()
    {
        return $this->executionFeedback;
    }

    public function setExecutionFeedback($executionFeedback)
    {
        $this->executionFeedback = $executionFeedback;
    }

    public function getReservation()
    {
        return $this->reservation;
    }

    public function setReservation($reservation)
    {
        $this->reservation = $reservation;
    }

    public function create()
    {
        $dao = new BookingReservationDAO();
        $r_data = [
            $this->reservation->getStart(),
            $this->reservation->getEnd(),
            $this->reservation->getType(),
            $this->reservation->getRequirement(),
            $this->reservation->getAdults(),
            $this->reservation->getChildren(),
            $this->reservation->getRequests(),
            $this->reservation->getTimestamp(),
            $this->reservation->getHash(),
            $this->pricing->getPricingId(),
            $this->pricing->getBookingId(),
            $this->pricing->getNights(),
            $this->pricing->getTotalPrice(),
            $this->pricing->getBookedDate()
        ];

        $c_data_string = implode(", ", $r_data);

        if(Util::has_reserved_words($c_data_string)) {
            $this->setExecutionFeedback(
                array(
                    "heading" => "",
                    "content" => "Something went wrong!",
                    "footer"  => "Aborting..."
                )
            );
            return "danger";
        } else {
            if ($dao->insert($this->reservation, $this->pricing)) {
                $this->setExecutionFeedback(
                    array(
                        "heading" => "Selamat!",
                        "content" => "Anda telah berhasil reservasi gedung",
                        "footer"  => "Reservasi anda akan ditandai dikonfirmasi setelah disetujui."
                    )
                );
                return "success";
            } else {
                $this->setExecutionFeedback("Server error! Please try again later.");
            }
        }
    }
}
