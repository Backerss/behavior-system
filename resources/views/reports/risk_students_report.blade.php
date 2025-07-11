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
            font-size: 16pt;
            font-weight: bold;
            margin-top: 10px;
            color: #dc3545;
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
            border-left: 4px solid #dc3545;
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
            font-size: 9pt;
        }
        
        .data-table th,
        .data-table td {
            border: 1px solid #000;
            padding: 4px;
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
        
        /* Risk Level Colors */
        .risk-very-high {
            background-color: #f8d7da !important;
            color: #721c24;
            font-weight: bold;
        }
        
        .risk-high {
            background-color: #f8d7da !important;
            color: #721c24;
        }
        
        .risk-medium {
            background-color: #fff3cd !important;
            color: #856404;
        }
        
        .risk-low {
            background-color: #d1ecf1 !important;
            color: #0c5460;
        }
        
        /* Summary Box */
        .summary-box {
            border: 1px solid #dc3545;
            padding: 10px;
            margin: 15px 0;
            background-color: #f8f9fa;
            font-size: 11pt;
        }
        
        .summary-header {
            font-weight: bold;
            font-size: 12pt;
            margin-bottom: 8px;
            color: #dc3545;
            border-bottom: 1px solid #dc3545;
            padding-bottom: 5px;
        }
        
        .summary-item {
            margin-bottom: 5px;
        }
        
        .summary-label {
            font-weight: bold;
            margin-right: 5px;
        }
        
        .risk-summary {
            display: inline-block;
            margin-right: 15px;
            padding: 5px 8px;
            border-radius: 3px;
            font-size: 10pt;
            font-weight: bold;
        }
        
        .risk-summary.very-high {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .risk-summary.high {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .risk-summary.medium {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .risk-summary.low {
            background-color: #d1ecf1;
            color: #0c5460;
        }
        
        /* Alert Box */
        .alert-box {
            border: 2px solid #dc3545;
            padding: 15px;
            margin: 15px 0;
            background-color: #fff5f5;
            font-size: 11pt;
        }
        
        .alert-header {
            font-weight: bold;
            font-size: 13pt;
            margin-bottom: 8px;
            color: #dc3545;
            text-align: center;
        }
        
        .alert-content {
            text-align: center;
            color: #333;
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
        
        /* Legend */
        .legend {
            margin: 10px 0;
            font-size: 9pt;
            text-align: center;
        }
        
        .legend-item {
            display: inline-block;
            margin: 0 8px;
            padding: 2px 6px;
            border-radius: 2px;
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
        <div class="report-title">รายงานสรุปนักเรียนที่มีความเสี่ยงประจำเดือน{{ $monthName }} พ.ศ. {{ $year + 543 }}</div>
        <div class="report-number">เลขที่: {{ 'RR-' . date('Y') . '-' . sprintf('%06d', rand(1, 999999)) }}</div>
    </div>

    <!-- Report Info Section -->
    <div class="section">
        <div class="section-header">รายละเอียดรายงาน</div>
        
        <div class="info-line">
            <span class="label">ระยะเวลารายงาน:</span>
            <span>{{ $reportPeriod }}</span>
        </div>
        
        <div class="info-line">
            <span class="label">ระดับความเสี่ยง:</span>
            <span>{{ $riskLevelText }}</span>
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

    <!-- Summary Alert -->
    @if(count($students) > 0)
    <div class="alert-box">
        <div class="alert-header">⚠️ สรุปความเสี่ยง</div>
        <div class="alert-content">
            พบนักเรียนที่มีความเสี่ยงทั้งสิ้น <strong>{{ count($students) }}</strong> คน จากทั้งหมด <strong>{{ $totalStudents }}</strong> คน
            <br>
            <div style="margin-top: 8px;">
                @if($summary['very_high'] > 0)
                    <span class="risk-summary very-high">ความเสี่ยงสูงมาก: {{ $summary['very_high'] }} คน</span>
                @endif
                @if($summary['high'] > 0)
                    <span class="risk-summary high">ความเสี่ยงสูง: {{ $summary['high'] }} คน</span>
                @endif
                @if($summary['medium'] > 0)
                    <span class="risk-summary medium">ความเสี่ยงปานกลาง: {{ $summary['medium'] }} คน</span>
                @endif
                @if($summary['low'] > 0)
                    <span class="risk-summary low">ความเสี่ยงต่ำ: {{ $summary['low'] }} คน</span>
                @endif
            </div>
        </div>
    </div>
    @endif
    
    <!-- Risk Students Table -->
    <div class="section">
        <div class="section-header">รายชื่อนักเรียนที่มีความเสี่ยง</div>
        
        <!-- Legend -->
        <div class="legend">
            <span class="legend-item risk-very-high">ความเสี่ยงสูงมาก</span>
            <span class="legend-item risk-high">ความเสี่ยงสูง</span>
            <span class="legend-item risk-medium">ความเสี่ยงปานกลาง</span>
            <span class="legend-item risk-low">ความเสี่ยงต่ำ</span>
        </div>
        
        <table class="data-table">
            <thead>
                <tr>
                    <th width="5%">ลำดับ</th>
                    <th width="10%">รหัสนักเรียน</th>
                    <th width="10%">ชั้น/ห้อง</th>
                    <th width="25%">ชื่อ-นามสกุล</th>
                    <th width="8%">จำนวนครั้ง</th>
                    <th width="8%">คะแนนหัก</th>
                    <th width="10%">คะแนนคงเหลือ</th>
                    <th width="12%">ระดับความเสี่ยง</th>
                    <th width="12%">ผู้ปกครอง/เบอร์โทร</th>
                </tr>
            </thead>
            <tbody>
                @if(count($students) > 0)
                    @foreach($students as $index => $student)
                        @php
                            $riskClass = '';
                            switch($student->risk_level) {
                                case 'very_high':
                                    $riskClass = 'risk-very-high';
                                    break;
                                case 'high':
                                    $riskClass = 'risk-high';
                                    break;
                                case 'medium':
                                    $riskClass = 'risk-medium';
                                    break;
                                case 'low':
                                    $riskClass = 'risk-low';
                                    break;
                            }
                        @endphp
                        <tr class="{{ $riskClass }}">
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
                            <td>{{ $student->violation_count }}</td>
                            <td>{{ $student->monthly_deducted_points }}</td>
                            <td>{{ $student->monthly_score }}/100</td>
                            <td>{{ $student->risk_level_text }}</td>
                            <td style="font-size: 8pt;">
                                @if($student->guardian && $student->guardian->user)
                                    {{ $student->guardian->user->users_first_name }} {{ $student->guardian->user->users_last_name }}
                                    <br>{{ $student->guardian->guardians_phone ?? '-' }}
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="9" class="text-center" style="color: #28a745; font-weight: bold;">
                            🎉 ไม่พบนักเรียนที่มีความเสี่ยงในเดือนนี้ - นักเรียนมีพฤติกรรมดีเยี่ยม
                        </td>
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
        <div class="summary-header">สรุปข้อมูลและข้อเสนอแนะ</div>
        <div class="summary-item">
            <span class="summary-label">• จำนวนนักเรียนทั้งหมด:</span>
            <span class="bold">{{ $totalStudents }} คน</span>
        </div>
        <div class="summary-item">
            <span class="summary-label">• จำนวนนักเรียนที่มีความเสี่ยง:</span>
            <span class="bold">{{ count($students) }} คน ({{ $totalStudents > 0 ? number_format((count($students) / $totalStudents) * 100, 1) : 0 }}%)</span>
        </div>
        <div class="summary-item">
            <span class="summary-label">• นักเรียนที่ต้องให้ความสำคัญเป็นพิเศษ:</span>
            <span class="bold">{{ $summary['very_high'] + $summary['high'] }} คน</span>
        </div>
        <div class="summary-item">
            <span class="summary-label">• คะแนนเฉลี่ยของนักเรียนที่มีความเสี่ยง:</span>
            <span class="bold">{{ count($students) > 0 ? number_format($students->avg('monthly_score'), 2) : 0 }}/100 คะแนน</span>
        </div>
        
        @if(count($students) > 0)
        <div style="margin-top: 10px; padding-top: 8px; border-top: 1px solid #ccc;">
            <div class="summary-item">
                <span class="summary-label">📋 ข้อเสนอแนะ:</span>
            </div>
            @if($summary['very_high'] > 0 || $summary['high'] > 0)
            <div class="summary-item" style="margin-left: 15px;">
                • ควรจัดกิจกรรมปรับพฤติกรรมสำหรับนักเรียนความเสี่ยงสูง
            </div>
            <div class="summary-item" style="margin-left: 15px;">
                • ติดต่อผู้ปกครองเพื่อร่วมมือในการแก้ไขพฤติกรรม
            </div>
            @endif
            @if($summary['medium'] > 0)
            <div class="summary-item" style="margin-left: 15px;">
                • เฝ้าระวังและให้คำแนะนำนักเรียนกลุ่มความเสี่ยงปานกลาง
            </div>
            @endif
            <div class="summary-item" style="margin-left: 15px;">
                • จัดกิจกรรมเสริมสร้างวินัยและความรับผิดชอบ
            </div>
        </div>
        @endif
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
