<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TasksExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $tasks;
    protected $serialNumber = 0;

    public function __construct($tasks)
    {
        $this->tasks = $tasks;
    }

    public function collection()
    {
        return $this->tasks;
    }

    public function headings(): array
    {
        return [
            '#',
            'Task Title',
            'Task Type',
            'Subtask',
            'Location',
            'Group',
            'From Date',
            'To Date',
            'Assigned Employees',
            'Created By',
            'Status',
            'Created At',
        ];
    }

    public function map($task): array
    {
        $this->serialNumber++;

        $assigned = $task->taskUsers->map(function ($tu) {
            return $tu->employee->user->name ?? null;
        })->filter()->implode(', ');

        $statusLabel = match($task->status) {
            'pending' => 'Pending',
            'in_progress' => 'In Progress',
            'completed' => 'Completed',
            default => ucfirst(str_replace('_', ' ', $task->status ?? ''))
        };

        return [
            $this->serialNumber,
            $task->title,
            ucfirst($task->task_type ?? ''),
            $task->subtask->subtask ?? ($task->subtask_id ? '-' : '-'),
            $task->location->name ?? '-',
            $task->group->group_name ?? '-',
            $task->from_date ?? '-',
            $task->to_date ?? '-',
            $assigned ?: '-',
            $task->createdBy->name ?? '-',
            $statusLabel,
            $task->created_at ? $task->created_at->format('Y-m-d H:i') : '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['argb' => 'FFFFFFFF'],
                    'size' => 11
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF1E293B']
                ]
            ],
        ];
    }
}
