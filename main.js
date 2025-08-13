const { app, BrowserWindow, ipcMain, dialog } = require('electron');
const path = require('path');

let mainWindow;

function createWindow() {
    mainWindow = new BrowserWindow({
        width: 1200,
        height: 800,
        webPreferences: {
            nodeIntegration: true,
            contextIsolation: false
        },
        icon: path.join(__dirname, 'assets/icon.png'),
        title: 'Исторический портрет',
        show: false
    });

    mainWindow.loadFile('index.html');

    mainWindow.once('ready-to-show', () => {
        mainWindow.show();
    });

    // Обработка сохранения файлов
    ipcMain.handle('save-file', async(event, imageData, fileName) => {
        const result = await dialog.showSaveDialog(mainWindow, {
            defaultPath: fileName,
            filters: [
                { name: 'Images', extensions: ['png', 'jpg', 'jpeg'] }
            ]
        });

        if (!result.canceled) {
            const fs = require('fs');
            const base64Data = imageData.replace(/^data:image\/png;base64,/, '');
            fs.writeFileSync(result.filePath, base64Data, 'base64');
            return true;
        }
        return false;
    });
}

app.whenReady().then(createWindow);

app.on('window-all-closed', () => {
    if (process.platform !== 'darwin') {
        app.quit();
    }
});

app.on('activate', () => {
    if (BrowserWindow.getAllWindows().length === 0) {
        createWindow();
    }
});