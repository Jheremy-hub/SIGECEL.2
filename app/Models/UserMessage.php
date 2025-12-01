<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserMessage extends Model
{
    protected $table = 'cms_user_messages';
    protected $primaryKey = 'id';

    protected $fillable = [
        'sender_id',
        'receiver_id',
        'intended_receiver_id',
        'approver_id',
        'subject',
        'message',
        'file_path',
        'file_name',
        'file_type',
        'file_size',
        'is_read',
        'status',
    ];

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function logs()
    {
        return $this->hasMany(UserMessageLog::class, 'message_id')->orderBy('created_at', 'desc');
    }

    public function forwards()
    {
        return $this->hasMany(UserMessageForward::class, 'original_message_id');
    }

    /**
     * Nivel de urgencia (normal, alta, critica) calculado desde el log de envío.
     */
    public function getUrgencyAttribute()
    {
        $log = $this->logs()
            ->where('action', 'sent')
            ->latest('created_at')
            ->first();

        if (!$log) {
            return 'normal';
        }

        $details = is_string($log->details)
            ? json_decode($log->details, true)
            : (is_array($log->details) ? $log->details : []);

        $value = $details['urgency'] ?? 'normal';

        return in_array($value, ['normal', 'alta', 'critica'], true) ? $value : 'normal';
    }

    public function getUrgencyLabelAttribute()
    {
        $map = [
            'normal'  => 'Normal',
            'alta'    => 'Alta',
            'critica' => 'Muy urgente',
        ];

        return $map[$this->urgency] ?? 'Normal';
    }

    public function getUrgencyBadgeClassAttribute()
    {
        $map = [
            'normal'  => 'bg-secondary',
            'alta'    => 'bg-warning text-dark',
            'critica' => 'bg-danger',
        ];

        return $map[$this->urgency] ?? 'bg-secondary';
    }

    /**
     * Status calculado del mensaje (incluye estados de jefe).
     */
    public function getStatusAttribute()
    {
        // Preferir el estado persistido si existe
        if (!empty($this->attributes['status'])) {
            return $this->attributes['status'];
        }

        // Prioridad: cancelled > archived > forwarded > read > sent
        if ($this->logs()->where('action', 'cancelled')->exists()) {
            return 'cancelled';
        }

        if ($this->logs()->where('action', 'archived')->exists()) {
            return 'archived';
        }

        if ($this->forwards()->exists()) {
            return 'forwarded';
        }

        if (!empty($this->attributes['is_read']) && $this->attributes['is_read']) {
            return 'read';
        }

        return 'sent';
    }

    public function getStatusLabelAttribute()
    {
        $map = [
            'sent' => 'Enviado',
            'read' => 'Leido',
            'forwarded' => 'Derivado',
            'archived' => 'Archivado',
            'cancelled' => 'Anulado',
            'pendiente_aprobacion_jefe' => 'Pendiente de jefe',
            'aprobado_por_jefe' => 'Aprobado por jefe',
            'archivado_por_jefe' => 'Archivado por jefe',
        ];

        return $map[$this->status] ?? ucfirst($this->status);
    }

    public function getStatusBadgeClassAttribute()
    {
        $map = [
            'sent' => 'bg-primary',
            'read' => 'bg-success',
            'forwarded' => 'bg-warning',
            'archived' => 'bg-secondary',
            'cancelled' => 'bg-danger',
            'pendiente_aprobacion_jefe' => 'bg-warning text-dark',
            'aprobado_por_jefe' => 'bg-success',
            'archivado_por_jefe' => 'bg-secondary',
        ];

        return $map[$this->status] ?? 'bg-secondary';
    }

    /**
     * Codigo formateado del mensaje: 00001-25
     */
    public function getCodeAttribute()
    {
        $id = $this->attributes['id'] ?? null;
        if (!$id) {
            return null;
        }

        $year = null;
        if (!empty($this->created_at)) {
            try {
                $year = $this->created_at->format('y');
            } catch (\Exception $e) {
                $year = \Carbon\Carbon::now()->format('y');
            }
        } else {
            $year = \Carbon\Carbon::now()->format('y');
        }

        return str_pad($id, 5, '0', STR_PAD_LEFT) . '-' . $year;
    }
}
