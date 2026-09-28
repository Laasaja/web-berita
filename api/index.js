const express = require('express');
const mongoose = require('mongoose');
const cors = require('cors');
require('dotenv').config();

const app = express();
app.use(express.json());
app.use(cors());

// Connect ke MongoDB Atlas
mongoose.connect(process.env.MONGODB_URI)
  .then(() => console.log('MongoDB Terhubung'))
  .catch(err => console.error('MongoDB Error:', err));

// Schema & Model Artikel
const articleSchema = new mongoose.Schema({
  title: String,
  status: String,
  published_at: Date,
  isi_berita: String
});
const Article = mongoose.model('Article', articleSchema, 'articles');

// Endpoint API Get Articles
app.get('/api/articles', async (req, res) => {
  try {
    const articles = await Article.find();
    res.json(articles);
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
});

module.exports = app;