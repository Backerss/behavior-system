/**
 * Module Loader for Behavior System
 * จัดการการโหลดและเริ่มต้น JavaScript modules
 */

class ModuleLoader {
    constructor() {
        this.modules = new Map();
        this.loadedModules = new Set();
        this.config = {
            basePath: '/js/modules/',
            timeout: 10000 // 10 seconds
        };
    }

    /**
     * ลงทะเบียน module
     */
    register(name, path, dependencies = []) {
        this.modules.set(name, {
            path,
            dependencies,
            loaded: false,
            instance: null
        });
    }

    /**
     * โหลด module
     */
    async load(name) {
        if (this.loadedModules.has(name)) {
            return this.modules.get(name)?.instance;
        }

        const moduleInfo = this.modules.get(name);
        if (!moduleInfo) {
            throw new Error(`Module "${name}" not registered`);
        }

        try {
            // โหลด dependencies ก่อน
            for (const dep of moduleInfo.dependencies) {
                await this.load(dep);
            }

            // โหลด module script
            await this.loadScript(moduleInfo.path);
            
            moduleInfo.loaded = true;
            this.loadedModules.add(name);
            
            return moduleInfo.instance;
        } catch (error) {
            console.error(`Error loading module "${name}":`, error);
            throw error;
        }
    }

    /**
     * โหลด script file
     */
    loadScript(path) {
        return new Promise((resolve, reject) => {
            // ตรวจสอบว่าโหลดแล้วหรือยัง
            const fullPath = this.config.basePath + path;
            if (document.querySelector(`script[src="${fullPath}"]`)) {
                resolve();
                return;
            }

            const script = document.createElement('script');
            script.src = fullPath;
            script.defer = true;
            
            const timeout = setTimeout(() => {
                reject(new Error(`Script loading timeout: ${path}`));
            }, this.config.timeout);

            script.onload = () => {
                clearTimeout(timeout);
                resolve();
            };

            script.onerror = () => {
                clearTimeout(timeout);
                reject(new Error(`Script loading failed: ${path}`));
            };

            document.head.appendChild(script);
        });
    }

    /**
     * โหลด multiple modules พร้อมกัน
     */
    async loadMultiple(names) {
        const promises = names.map(name => this.load(name));
        return Promise.all(promises);
    }

    /**
     * ตรวจสอบว่า module โหลดแล้วหรือยัง
     */
    isLoaded(name) {
        return this.loadedModules.has(name);
    }

    /**
     * ดึง module instance
     */
    getInstance(name) {
        return this.modules.get(name)?.instance;
    }
}

/**
 * Behavior System Module Manager
 * จัดการ modules เฉพาะสำหรับ behavior system
 */
class BehaviorSystemModules {
    constructor() {
        this.loader = new ModuleLoader();
        this.initializeModules();
    }

    /**
     * ลงทะเบียน modules ทั้งหมด
     */
    initializeModules() {
        // Core modules
        this.loader.register('utils', 'utils.js');
        this.loader.register('api-client', 'api-client.js', ['utils']);
        this.loader.register('notification', 'notification.js', ['utils']);
        
        // Feature modules
        this.loader.register('student-manager', 'student-manager.js', ['api-client', 'notification']);
        this.loader.register('behavior-report-manager', 'behavior-report-manager.js', ['student-manager', 'api-client']);
        this.loader.register('dashboard-charts', 'dashboard-charts.js', ['utils']);
        this.loader.register('class-manager', 'class-manager.js', ['api-client']);
        
        // Page-specific modules
        this.loader.register('teacher-dashboard', 'teacher-dashboard.js', [
            'student-manager', 
            'behavior-report-manager', 
            'dashboard-charts'
        ]);
        this.loader.register('parent-dashboard', 'parent-dashboard.js', ['dashboard-charts', 'notification']);
        this.loader.register('student-dashboard', 'student-dashboard.js', ['dashboard-charts']);
    }

    /**
     * โหลด modules ตามหน้า
     */
    async loadForPage(pageName) {
        const pageModules = this.getPageModules(pageName);
        
        try {
            await this.loader.loadMultiple(pageModules);
        } catch (error) {
            console.error(`Error loading modules for page ${pageName}:`, error);
        }
    }

    /**
     * กำหนด modules ที่ต้องใช้ในแต่ละหน้า
     */
    getPageModules(pageName) {
        const pageModuleMap = {
            'teacher-dashboard': ['teacher-dashboard'],
            'parent-dashboard': ['parent-dashboard'],
            'student-dashboard': ['student-dashboard'],
            'behavior-report': ['behavior-report-manager'],
            'class-management': ['class-manager'],
            'all': ['student-manager', 'behavior-report-manager', 'dashboard-charts', 'notification']
        };

        return pageModuleMap[pageName] || [];
    }

    /**
     * ดึง module instance
     */
    get(moduleName) {
        return this.loader.getInstance(moduleName);
    }

    /**
     * ตรวจสอบว่า module พร้อมใช้งานหรือยัง
     */
    isReady(moduleName) {
        return this.loader.isLoaded(moduleName);
    }
}

// สร้าง global instance
window.behaviorModules = new BehaviorSystemModules();

/**
 * Auto-load modules based on page
 */
document.addEventListener('DOMContentLoaded', () => {
    // ตรวจสอบหน้าปัจจุบันจาก body class หรือ data attribute
    const body = document.body;
    const pageName = body.dataset.page || 
                    body.className.split(' ').find(cls => cls.endsWith('-page'))?.replace('-page', '') ||
                    'all';

    // โหลด modules สำหรับหน้านี้
    window.behaviorModules.loadForPage(pageName)
        .then(() => {
            // Trigger event เมื่อโหลดเสร็จ
            const event = new CustomEvent('modulesLoaded', {
                detail: { page: pageName }
            });
            document.dispatchEvent(event);
        })
        .catch(error => {
            console.error('Module loading failed:', error);
        });
});

/**
 * Utility function สำหรับโหลด module แบบ on-demand
 */
window.loadModule = async function(moduleName) {
    try {
        return await window.behaviorModules.loader.load(moduleName);
    } catch (error) {
        console.error(`Failed to load module "${moduleName}":`, error);
        return null;
    }
};

/**
 * Utility function สำหรับใช้ module
 */
window.useModule = function(moduleName, callback) {
    if (window.behaviorModules.isReady(moduleName)) {
        const module = window.behaviorModules.get(moduleName);
        callback(module);
    } else {
        window.loadModule(moduleName).then(module => {
            if (module) callback(module);
        });
    }
};
