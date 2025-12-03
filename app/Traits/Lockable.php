<?php

namespace App\Traits;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

trait Lockable
{
  public function lockRecord($userId = null, $timeout = 300)
  {
    $userId = $userId ?? auth()->id();

    Cache::put($this->getRecordLockKey(), [
      'user_id' => $userId,
      'locked_at' => now()->timestamp,
      'expires_at' => now()->addSeconds($timeout)->timestamp,
    ], $timeout);

    return $this;
  }

  public function unlockRecord()
  {
    Cache::forget($this->getRecordLockKey());
    return $this;
  }

  public function isRecordLocked()
  {
    return Cache::has($this->getRecordLockKey());
  }

  public function getRecordLockInfo()
  {
    return Cache::get($this->getRecordLockKey());
  }

  public function isRecordLockedByCurrentUser()
  {
    $lock = $this->getRecordLockInfo();
    return $lock && $lock['user_id'] === auth()->id();
  }

  public function getLockTimeLeft(): ?string
  {
    $lock = $this->getRecordLockInfo();

    if (!$lock || !isset($lock['expires_at'])) {
      return null;
    }

    $expiresAt = Carbon::createFromTimestamp($lock['expires_at']);
    $now = now();

    if ($expiresAt->isPast()) {
      $this->unlockRecord();
      return null;
    }

    return $now->diffForHumans($expiresAt, [
      'syntax' => Carbon::DIFF_ABSOLUTE,
      'parts' => 2,
      'short' => false,
    ]);
  }

  public function getLockTimeLeftInSeconds(): ?int
  {
    $lock = $this->getRecordLockInfo();

    if (!$lock || !isset($lock['expires_at'])) {
      return null;
    }

    $expiresAt = Carbon::createFromTimestamp($lock['expires_at']);
    $secondsLeft = $expiresAt->diffInSeconds(now(), false);

    if ($secondsLeft <= 0) {
      $this->unlockRecord();
      return null;
    }

    return $secondsLeft;
  }

  public function getLockTimeLeftProgress(): ?float
  {
    $lock = $this->getRecordLockInfo();

    if (!$lock || !isset($lock['expires_at']) || !isset($lock['locked_at'])) {
      return null;
    }

    $totalLockTime = $lock['expires_at'] - $lock['locked_at'];
    $timeLeft = $lock['expires_at'] - now()->timestamp;

    if ($timeLeft <= 0) {
      $this->unlockRecord();
      return null;
    }

    return ($timeLeft / $totalLockTime) * 100;
  }

  public function getLockedByUser()
  {
    $lock = $this->getRecordLockInfo();

    if (!$lock || !isset($lock['user_id'])) {
      return null;
    }

    if (class_exists(\App\Models\User::class)) {
      return \App\Models\User::find($lock['user_id']);
    }

    return null;
  }

  public function getLockedAt(): ?Carbon
  {
    $lock = $this->getRecordLockInfo();

    if (!$lock || !isset($lock['locked_at'])) {
      return null;
    }

    return Carbon::createFromTimestamp($lock['locked_at']);
  }

  public function getExpiresAt(): ?Carbon
  {
    $lock = $this->getRecordLockInfo();

    if (!$lock || !isset($lock['expires_at'])) {
      return null;
    }

    return Carbon::createFromTimestamp($lock['expires_at']);
  }

  public function refreshLock($timeout = 300)
  {
    $lock = $this->getRecordLockInfo();

    if (!$lock) {
      return $this->lockRecord(null, $timeout);
    }

    if ($lock['user_id'] === auth()->id()) {
      return $this->lockRecord($lock['user_id'], $timeout);
    }

    return $this;
  }

  public function getLockStatusMessage(): string
  {
    if (!$this->isRecordLocked()) {
      return 'Disponible a editar';
    }

    if ($this->isRecordLockedByCurrentUser()) {
      $timeLeft = $this->getLockTimeLeft();
      return "Bloqueado por ti. Se desbloquea en {$timeLeft}";
    }

    $lockedBy = $this->getLockedByUser();
    $timeLeft = $this->getLockTimeLeft();

    if ($lockedBy) {
      return "Bloqueado por {$lockedBy->name}. Se desbloquea en {$timeLeft}";
    }

    return "Bloqueado por otro usuario. Se desbloquea en {$timeLeft}";
  }

  protected function getRecordLockKey(): string
  {
    return "record_lock:{$this->getTable()}:{$this->getKey()}";
  }
}