const express = require('express');
const http = require('http');
const { Server } = require('socket.io');
const cors = require('cors');
const jwt = require('jsonwebtoken');
const redis = require('redis');

const JWT_SECRET = '9Vsb3Y6236VLjqrB8PhE7GkrC2QKisTRBaaWWDHIiY1LzoL15A7No7gvUN0eTbf0'; // 🛡️ نفس القيمة من .env في Laravel

const app = express();
app.use(cors());
app.use(express.json());

const server = http.createServer(app);
const io = new Server(server, {
    cors: {
        origin: '*', // في الإنتاج، حدّد نطاق الفرونت فقط
        methods: ['GET', 'POST']
    }
});

const redisClient = redis.createClient();
redisClient.connect().catch(console.error);

io.use((socket, next) => {
    const token = socket.handshake.auth.token;

    if (!token) {
        console.log('⛔ No token provided');
        return next(new Error('Unauthorized'));
    }

    try {
        const decoded = jwt.verify(token, JWT_SECRET); 
        socket.user = decoded; 
        console.log('🔐 Authenticated:', decoded);
        next();
    } catch (err) {
        console.log('⛔ Invalid token');
        return next(new Error('Unauthorized'));
    }
});

app.post('/broadcast', async (req, res) => {
    const { room, message } = req.body;
    const cacheKey = 'broadcasted_' + message.id;

    try {
        const added = await redisClient.set(cacheKey, '1', {
            NX: true,
            EX: 43200 
        });

        if (!added) {
            console.log('⛔ تم تجاهل رسالة مكررة ID:', message.id);
            return res.status(200).json({ success: false, duplicated: true });
        }

        io.to(room).emit('receive_message', message);
        console.log(`📢 Broadcasted to ${room}:`, message);
        res.send({ success: true });

    } catch (error) {
        console.error('⚠️ Error broadcasting message:', error);
        res.status(500).send({ success: false, error: error.message });
    }
});

io.on('connection', (socket) => {
    console.log('🔌 User connected:', socket.id, '| User:', socket.user);

    socket.on('join_room', (room) => {
        socket.join(room);
        console.log(`${socket.id} joined room: ${room}`);
    });

    socket.on('disconnect', () => {
        console.log('❌ User disconnected:', socket.id);
    });
});

server.listen(3000, () => {
    console.log('🚀 Socket.IO server running at http://localhost:3000');
});
