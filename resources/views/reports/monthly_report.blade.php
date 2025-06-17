<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: "DejaVu Sans", sans-serif;
            font-size: 12pt;
            line-height: 1.3;
            margin: 15px;
            padding: 0;
            color: #000;
        }
        
        /* Header */
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }
        
        .logo {
            margin-bottom: 10px;
        }
        
        .logo img {
            width: 80px;
            height: 80px;
            margin: 0 auto;
        }
        
        .school-name {
            font-size: 18pt;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .school-address {
            font-size: 11pt;
            margin-bottom: 5px;
            color: #333;
        }
        
        .report-title {
            font-size: 11pt;
            margin-top: 10px;
        }
        
        .report-number {
            font-size: 11pt;
            margin-top: 5px;
            color: #666;
        }
        
        /* Report Info */
        .section {
            margin-bottom: 15px;
        }
        
        .section-header {
            font-size: 14pt;
            font-weight: bold;
            background-color: #f0f0f0;
            padding: 8px;
            margin-bottom: 10px;
            border-left: 4px solid #1020AD;
        }
        
        .info-line {
            margin-bottom: 8px;
            font-size: 11pt;
        }
        
        .label {
            font-weight: bold;
            width: 150px;
            display: inline-block;
        }
        
        /* Table */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
            font-size: 10pt;
        }
        
        .data-table th,
        .data-table td {
            border: 1px solid #000;
            padding: 5px;
            text-align: center;
            vertical-align: middle;
        }
        
        .data-table th {
            background-color: #e9ecef;
            font-weight: bold;
            text-align: center;
        }
        
        .data-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        /* Summary Box */
        .summary-box {
            border: 1px solid #6c757d;
            padding: 10px;
            margin: 15px 0;
            background-color: #f8f9fa;
            font-size: 11pt;
        }
        
        .summary-header {
            font-weight: bold;
            font-size: 12pt;
            margin-bottom: 8px;
            color: #495057;
            border-bottom: 1px solid #6c757d;
            padding-bottom: 5px;
        }
        
        .summary-item {
            margin-bottom: 5px;
        }
        
        .summary-label {
            font-weight: bold;
            margin-right: 5px;
        }
        
        /* Signatures */
        .signatures {
            margin-top: 30px;
            font-size: 11pt;
            clear: both;
        }
        
        .sig-table {
            width: 100%;
        }
        
        .sig-table td {
            text-align: center;
            padding: 15px 5px;
            width: 33.33%;
            vertical-align: top;
        }
        
        .sig-line {
            border-bottom: 1px solid #000;
            width: 150px;
            margin: 45px auto 8px;
        }
        
        /* Footer */
        .footer {
            font-size: 9pt;
            color: #666;
            margin-top: 20px;
            border-top: 1px solid #ddd;
            padding-top: 8px;
            text-align: center;
            clear: both;
        }
        
        /* Color Highlights */
        .bg-warning {
            background-color: #fff3cd !important;
        }
        
        .bg-danger {
            background-color: #f8d7da !important;
        }
        
        /* Utility Classes */
        .text-center {
            text-align: center;
        }
        
        .text-left {
            text-align: left;
        }
        
        .text-right {
            text-align: right;
        }
        
        .bold {
            font-weight: bold;
        }
        
        .clear {
            clear: both;
        }
        
        /* Document Reference */
        .document-ref {
            text-align: right;
            font-size: 10pt;
            color: #444;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="logo">
            <img src="{{ public_path('images/logo.png') }}" alt="Logo โรงเรียน" style="width: 80px; height: 80px;">
        </div>
        <div class="school-name">โรงเรียนนวมินทราชูทิศ มัชฌิม</div>
        <div class="school-address">เลขที่ 24/20 ตำบลนครสวรรค์ตก อำเภอเมืองนครสวรรค์ จังหวัดนครสวรรค์ 60000</div>
        <div class="report-title">รายงานพฤติกรรมและความประพฤตินักเรียนประจำเดือน{{ $monthName }} พ.ศ. {{ $year + 543 }}</div>
        <div class="report-number">เลขที่: {{ 'MR-' . date('Y') . '-' . sprintf('%06d', rand(1, 999999)) }}</div>
    </div>

    <!-- Report Info Section -->
    <div class="section">
        <div class="section-header">รายละเอียดรายงาน</div>
        
        <div class="info-line">
            <span class="label">ระยะเวลารายงาน:</span>
            <span>{{ $reportPeriod }}</span>
        </div>
        
        @if($classroom)
        <div class="info-line">
            <span class="label">ชั้น/ห้อง:</span>
            <span>{{ $classroom->classes_level }}/{{ $classroom->classes_room_number }}</span>
        </div>
        <div class="info-line">
            <span class="label">ครูประจำชั้น:</span>
            <span>
                @if($classroom->teacher && $classroom->teacher->user)
                    {{ $classroom->teacher->user->users_name_prefix }}{{ $classroom->teacher->user->users_first_name }} {{ $classroom->teacher->user->users_last_name }}
                @else
                    -
                @endif
            </span>
        </div>
        @endif
    </div>
    
    <!-- Student Behavior Table -->
    <div class="section">
        <div class="section-header">ข้อมูลพฤติกรรมนักเรียน</div>
        
        <table class="data-table">
            <thead>
                <tr>
                    <th width="5%">ลำดับ</th>
                    <th width="10%">รหัสนักเรียน</th>
                    <th width="10%">ชั้น/ห้อง</th>
                    <th width="35%">ชื่อ-นามสกุล</th>
                    <th width="12%">จำนวนครั้งที่ทำผิด</th>
                    <th width="12%">คะแนนที่หัก</th>
                    <th width="16%">คะแนนคงเหลือ</th>
                </tr>
            </thead>
            <tbody>
                @if(count($students) > 0)
                    @foreach($students as $index => $student)
                        @php
                            $violationsCount = $student->behaviorReports->count();
                            $rowClass = '';
                            if ($student->monthly_score <= 60) {
                                $rowClass = 'bg-danger';
                            } elseif ($student->monthly_score <= 75) {
                                $rowClass = 'bg-warning';
                            }
                        @endphp
                        <tr class="{{ $rowClass }}">
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $student->students_student_code }}</td>
                            <td>
                                @if($student->classroom)
                                    {{ $student->classroom->classes_level }}/{{ $student->classroom->classes_room_number }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-left">
                                @if($student->user)
                                    {{ $student->user->users_name_prefix }}{{ $student->user->users_first_name }} {{ $student->user->users_last_name }}
                                @else
                                    ไม่มีข้อมูล
                                @endif
                            </td>
                            <td>{{ $violationsCount }}</td>
                            <td>{{ $student->monthly_deducted_points }}</td>
                            <td>{{ $student->monthly_score }}/100</td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="7" class="text-center">ไม่พบข้อมูลนักเรียนในเดือนนี้</td>
                    </tr>
                @endif
            </tbody>
        </table>
        
        @if(count($students) > 25)
            <div style="text-align: center; font-size: 9pt; color: #666;">
                * แสดงผลข้อมูลจำนวนทั้งสิ้น {{ count($students) }} คน
            </div>
        @endif
    </div>
    
    <!-- Summary Section -->
    <div class="summary-box">
        <div class="summary-header">สรุปข้อมูลพฤติกรรมนักเรียนประจำเดือน</div>
        <div class="summary-item">
            <span class="summary-label">• จำนวนนักเรียนทั้งหมด:</span>
            <span class="bold">{{ count($students) }} คน</span>
        </div>
        <div class="summary-item">
            <span class="summary-label">• จำนวนนักเรียนที่มีพฤติกรรมผิดระเบียบ:</span>
            <span class="bold">{{ $students->filter(function($student) { return count($student->behaviorReports) > 0; })->count() }} คน</span>
        </div>
        <div class="summary-item">
            <span class="summary-label">• จำนวนนักเรียนที่มีคะแนนต่ำกว่า 75 คะแนน:</span>
            <span class="bold">{{ $students->filter(function($student) { return $student->monthly_score < 75; })->count() }} คน</span>
        </div>
        <div class="summary-item">
            <span class="summary-label">• จำนวนนักเรียนที่มีคะแนนต่ำกว่า 60 คะแนน:</span>
            <span class="bold">{{ $students->filter(function($student) { return $student->monthly_score < 60; })->count() }} คน</span>
        </div>
        <div class="summary-item">
            <span class="summary-label">• คะแนนเฉลี่ยของนักเรียนทั้งหมด:</span>
            <span class="bold">{{ $students->count() > 0 ? number_format($students->avg('monthly_score'), 2) : 0 }}/100 คะแนน</span>
        </div>
    </div>
    
    <!-- Signatures -->
    <div class="signatures">
        <table class="sig-table">
            <tr>
                <td>
                    <div style="margin-bottom: 40px;">
                        <div style="margin-top: 20px;">
                            ลงชื่อ....................................
                        </div>
                        <div style="font-size: 9pt; margin-top: 10px;">
                            (....................................)
                        </div>
                        <div style="font-size: 10pt; margin-top: 3px;">
                            ผู้จัดทำรายงาน
                        </div>
                    </div>
                </td>
                <td>
                    <div style="margin-bottom: 40px;">
                        <div style="margin-top: 20px;">
                            ลงชื่อ....................................
                        </div>
                        <div style="font-size: 9pt; margin-top: 10px;">
                            (....................................)
                        </div>
                        <div style="font-size: 10pt; margin-top: 3px;">
                            หัวหน้างานระดับชั้น
                        </div>
                    </div>
                </td>
                <td>
                    <div style="margin-bottom: 40px;">
                        <div style="margin-top: 20px;">
                            ลงชื่อ....................................
                        </div>
                        <div style="font-size: 9pt; margin-top: 5px;">
                            (นายพงษ์เทพ เจริญไทย)
                        </div>
                        <div style="font-size: 10pt; margin-top: 3px;">
                            ผู้อำนวยการสถานศึกษา
                        </div>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Footer -->
    <div class="footer">
        พิมพ์: {{ $generatedAt->format('d/m/Y H:i') }} | 
        โดย: {{ auth()->user()->users_first_name ?? 'ระบบ' }} {{ auth()->user()->users_last_name ?? '' }} | 
        เอกสารนี้ออกโดยระบบบริหารจัดการพฤติกรรมนักเรียน
    </div>
</body>
</html>