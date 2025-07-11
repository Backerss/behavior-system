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
            color: #1020AD;
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
        
        /* Category Colors */
        .category-light {
            background-color: #d1ecf1 !important;
            color: #0c5460;
        }
        
        .category-medium {
            background-color: #fff3cd !important;
            color: #856404;
        }
        
        .category-severe {
            background-color: #f8d7da !important;
            color: #721c24;
        }
        
        /* Summary Box */
        .summary-box {
            border: 1px solid #1020AD;
            padding: 10px;
            margin: 15px 0;
            background-color: #f8f9fa;
            font-size: 11pt;
        }
        
        .summary-header {
            font-weight: bold;
            font-size: 12pt;
            margin-bottom: 8px;
            color: #1020AD;
            border-bottom: 1px solid #1020AD;
            padding-bottom: 5px;
        }
        
        .summary-item {
            margin-bottom: 5px;
        }
        
        .summary-label {
            font-weight: bold;
            margin-right: 5px;
        }
        
        .stats-box {
            display: inline-block;
            margin-right: 15px;
            padding: 5px 8px;
            border-radius: 3px;
            font-size: 10pt;
            font-weight: bold;
        }
        
        .stats-box.light {
            background-color: #d1ecf1;
            color: #0c5460;
        }
        
        .stats-box.medium {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .stats-box.severe {
            background-color: #f8d7da;
            color: #721c24;
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
        <div class="report-title">รายงานข้อมูลพฤติกรรมนักเรียนทั้งหมดประจำเดือน{{ $monthName }} พ.ศ. {{ $year + 543 }}</div>
        <div class="report-number">เลขที่: {{ 'BR-' . date('Y') . '-' . sprintf('%06d', rand(1, 999999)) }}</div>
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
        
        <div class="info-line">
            <span class="label">จำนวนรายงานทั้งหมด:</span>
            <span class="bold">{{ $totalReports }} รายการ</span>
        </div>
        
        <div class="info-line">
            <span class="label">คะแนนที่หักทั้งหมด:</span>
            <span class="bold">{{ $totalPoints }} คะแนน</span>
        </div>
    </div>
    
    <!-- Statistics Summary -->
    <div class="summary-box">
        <div class="summary-header">สรุปสถิติพฤติกรรมตามประเภท</div>
        <div style="text-align: center; margin: 10px 0;">
            <span class="stats-box light">เบา: {{ $categoryStats['light'] }} ครั้ง</span>
            <span class="stats-box medium">ปานกลาง: {{ $categoryStats['medium'] }} ครั้ง</span>
            <span class="stats-box severe">รุนแรง: {{ $categoryStats['severe'] }} ครั้ง</span>
        </div>
    </div>
    
    <!-- All Behavior Reports Table -->
    <div class="section">
        <div class="section-header">รายการข้อมูลพฤติกรรมทั้งหมด</div>
        
        <table class="data-table">
            <thead>
                <tr>
                    <th width="5%">ลำดับ</th>
                    <th width="8%">วันที่</th>
                    <th width="10%">รหัสนักเรียน</th>
                    <th width="20%">ชื่อ-นามสกุล</th>
                    <th width="8%">ชั้น/ห้อง</th>
                    <th width="20%">พฤติกรรม</th>
                    <th width="8%">ประเภท</th>
                    <th width="6%">คะแนนหัก</th>
                    <th width="15%">ครูผู้บันทึก</th>
                </tr>
            </thead>
            <tbody>
                @if(count($allReports) > 0)
                    @foreach($allReports as $index => $item)
                        @php
                            $student = $item['student'];
                            $report = $item['report'];
                            $category = $item['category'];
                            
                            $categoryClass = '';
                            $categoryText = '';
                            switch($category) {
                                case 'light':
                                    $categoryClass = 'category-light';
                                    $categoryText = 'เบา';
                                    break;
                                case 'medium':
                                    $categoryClass = 'category-medium';
                                    $categoryText = 'ปานกลาง';
                                    break;
                                case 'severe':
                                    $categoryClass = 'category-severe';
                                    $categoryText = 'รุนแรง';
                                    break;
                                default:
                                    $categoryClass = '';
                                    $categoryText = '-';
                            }
                        @endphp
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                @if($report->reports_report_date)
                                    {{ \Carbon\Carbon::parse($report->reports_report_date)->format('d/m/y') }}
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ $student->students_student_code }}</td>
                            <td class="text-left">
                                @if($student->user)
                                    {{ $student->user->users_name_prefix }}{{ $student->user->users_first_name }} {{ $student->user->users_last_name }}
                                @else
                                    ไม่มีข้อมูล
                                @endif
                            </td>
                            <td>
                                @if($student->classroom)
                                    {{ $student->classroom->classes_level }}/{{ $student->classroom->classes_room_number }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-left">
                                {{ $report->violation ? $report->violation->violations_name : 'ไม่มีข้อมูล' }}
                            </td>
                            <td class="{{ $categoryClass }}">{{ $categoryText }}</td>
                            <td>{{ $item['points'] }}</td>
                            <td style="font-size: 8pt;">
                                @if($report->teacher && $report->teacher->user)
                                    {{ $report->teacher->user->users_first_name }} {{ $report->teacher->user->users_last_name }}
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="9" class="text-center" style="color: #28a745; font-weight: bold;">
                            🎉 ไม่พบข้อมูลการบันทึกพฤติกรรมในเดือนนี้
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
        
        @if(count($allReports) > 25)
            <div style="text-align: center; font-size: 9pt; color: #666;">
                * แสดงผลข้อมูลจำนวนทั้งสิ้น {{ count($allReports) }} รายการ
            </div>
        @endif
    </div>
    
    <!-- Student Summary Table -->
    <div class="section">
        <div class="section-header">สรุปคะแนนพฤติกรรมนักเรียน</div>
        
        <table class="data-table">
            <thead>
                <tr>
                    <th width="5%">ลำดับ</th>
                    <th width="10%">รหัสนักเรียน</th>
                    <th width="35%">ชื่อ-นามสกุล</th>
                    <th width="10%">ชั้น/ห้อง</th>
                    <th width="15%">จำนวนครั้งที่ทำผิด</th>
                    <th width="10%">คะแนนหัก</th>
                    <th width="15%">คะแนนคงเหลือ</th>
                </tr>
            </thead>
            <tbody>
                @if(count($students) > 0)
                    @foreach($students as $index => $student)
                        @php
                            $violationsCount = $student->behaviorReports->count();
                            $rowClass = '';
                            if ($student->monthly_score <= 60) {
                                $rowClass = 'category-severe';
                            } elseif ($student->monthly_score <= 75) {
                                $rowClass = 'category-medium';
                            }
                        @endphp
                        <tr class="{{ $rowClass }}">
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $student->students_student_code }}</td>
                            <td class="text-left">
                                @if($student->user)
                                    {{ $student->user->users_name_prefix }}{{ $student->user->users_first_name }} {{ $student->user->users_last_name }}
                                @else
                                    ไม่มีข้อมูล
                                @endif
                            </td>
                            <td>
                                @if($student->classroom)
                                    {{ $student->classroom->classes_level }}/{{ $student->classroom->classes_room_number }}
                                @else
                                    -
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
    </div>
    
    <!-- Summary Section -->
    <div class="summary-box">
        <div class="summary-header">สรุปข้อมูลพฤติกรรมนักเรียนทั้งหมด</div>
        <div class="summary-item">
            <span class="summary-label">• จำนวนนักเรียนทั้งหมด:</span>
            <span class="bold">{{ count($students) }} คน</span>
        </div>
        <div class="summary-item">
            <span class="summary-label">• จำนวนนักเรียนที่มีพฤติกรรมผิดระเบียบ:</span>
            <span class="bold">{{ $students->filter(function($student) { return count($student->behaviorReports) > 0; })->count() }} คน</span>
        </div>
        <div class="summary-item">
            <span class="summary-label">• จำนวนรายงานพฤติกรรมทั้งหมด:</span>
            <span class="bold">{{ $totalReports }} รายการ</span>
        </div>
        <div class="summary-item">
            <span class="summary-label">• คะแนนที่หักไปทั้งหมด:</span>
            <span class="bold">{{ $totalPoints }} คะแนน</span>
        </div>
        <div class="summary-item">
            <span class="summary-label">• คะแนนเฉลี่ยของนักเรียนทั้งหมด:</span>
            <span class="bold">{{ $students->count() > 0 ? number_format($students->avg('monthly_score'), 2) : 0 }}/100 คะแนน</span>
        </div>
        
        <div style="margin-top: 10px; padding-top: 8px; border-top: 1px solid #ccc;">
            <div class="summary-item">
                <span class="summary-label">📊 สถิติตามประเภทพฤติกรรม:</span>
            </div>
            <div class="summary-item" style="margin-left: 15px;">
                • พฤติกรรมประเภทเบา: {{ $categoryStats['light'] }} ครั้ง ({{ $totalReports > 0 ? number_format(($categoryStats['light'] / $totalReports) * 100, 1) : 0 }}%)
            </div>
            <div class="summary-item" style="margin-left: 15px;">
                • พฤติกรรมประเภทปานกลาง: {{ $categoryStats['medium'] }} ครั้ง ({{ $totalReports > 0 ? number_format(($categoryStats['medium'] / $totalReports) * 100, 1) : 0 }}%)
            </div>
            <div class="summary-item" style="margin-left: 15px;">
                • พฤติกรรมประเภทรุนแรง: {{ $categoryStats['severe'] }} ครั้ง ({{ $totalReports > 0 ? number_format(($categoryStats['severe'] / $totalReports) * 100, 1) : 0 }}%)
            </div>
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
