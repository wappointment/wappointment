<?php

namespace Wappointment\Jobs;

abstract class AbstractEmailJob extends AbstractTransportableJob
{
    use IsEmailableJob, IsAdminAppointmentJob;
}
