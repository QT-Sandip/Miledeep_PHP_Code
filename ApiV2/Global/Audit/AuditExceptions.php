<?php

class AuditException extends Exception
{
}

class AuditConfigurationException extends AuditException
{
}

class AuditQueueException extends AuditException
{
}

class AuditWriteException extends AuditException
{
}
