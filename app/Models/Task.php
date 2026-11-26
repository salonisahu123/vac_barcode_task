<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'user_id',
        'dashboard_task_id',
        'subtask_id',
        'task_type',
        'dash_task_type',
        'mode',
        'location_id',
        'group_id',
        'status',
        'from_date',
        'to_date',
        'description',
        'file',
    ];

    public function taskUsers()
    {
        return $this->hasMany(TaskUser::class, 'task_id');
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    
        public function dashboardTask()
    {
        return $this->belongsTo(DashboardTask::class, 'dashboard_task_id');
    }
    
        public function subtask()
    {
        return $this->belongsTo(Subtask::class);
    }

    public function activities()
    {
        return $this->hasMany(Activity::class, 'table_id')
            ->where('table_name', 'tasks');
    }

    public function remarks()
    {
        return $this->hasMany(Activity::class, 'table_id')
            ->where('table_name', 'tasks')
            ->where('operation', 'remark')
            ->where('description', 'not like', '%status changed%')
            ->where('description', 'not like', '%subtask changed%');
    }

    public function latestRemark()
    {
        return $this->hasOne(Activity::class, 'table_id')
            ->ofMany(
                ['id' => 'max'],
                function ($query) {
                    $query->where('table_name', 'tasks')
                        ->where('operation', 'remark')
                        ->where('description', 'not like', '%status changed%')
                        ->where('description', 'not like', '%subtask changed%');
                }
            );
    }

    public function getLatestRemarkTextAttribute()
    {
        if (!$this->latestRemark) {
            return null;
        }

        $desc = $this->latestRemark->description;

        if (stripos($desc, 'status changed') !== false || stripos($desc, 'subtask changed') !== false) {
            return null;
        }

        if (preg_match('/^Remark added on task ".*":\s*(.*)$/s', $desc, $matches)) {
            return $matches[1];
        }

        if (str_contains($desc, '": ')) {
            return \Illuminate\Support\Str::after($desc, '": ');
        }

        return $desc;
    }
}


