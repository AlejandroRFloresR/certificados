<?php

namespace App\Services;

use RuntimeException;

/** El usuario ya tiene un certificado de este curso, pero de otro tipo */
class CertificateTypeConflict extends RuntimeException
{
}
