from flask import Flask
from flask import jsonify
from flask_restful import Api, Resource, abort, fields, marshal_with
from flask_sqlalchemy import SQLAlchemy
from flaskext.mysql import MySQL
import MySQLdb as sql
from levenshtein import match
import base64

app = Flask(__name__)
api = Api(app)
mysql = MySQL()
app.config['SQLALCHEMY_DATABASE_URI'] = "mysql://zib:Z00m1nt0B00ks@localhost/zib"
db = SQLAlchemy(app)

class PublisherModel(db.Model):
	__tablename__ = "publisher"

	publisher = db.Column(db.String, primary_key=True)
	email = db.Column(db.String)

class BookModel(db.Model):	 
	__tablename__ = "book"
    
	isbn = db.Column(db.Integer, primary_key=True)
	title = db.Column(db.String)
	author = db.Column(db.String)
	edition = db.Column(db.String)
	publisher = db.Column(db.String, db.ForeignKey("publisher.publisher"))
	book_type = db.Column(db.String)
	ar_blob = db.Column(db.LargeBinary)
	ocr_blob = db.Column(db.String)
    
class ResourceModel(db.Model):
	__tablename__ =  "resource"

	rid = db.Column(db.Integer, primary_key=True)
	name = db.Column(db.String)
	url = db.Column(db.String)
	display = db.Column(db.String)
	downloadable = db.Column(db.Boolean)
	resource_type = db.Column(db.String)
    
class ARResourceModel(db.Model):
	__tablename__ = "ar_resource_link"

	isbn = db.Column(db.Integer, db.ForeignKey("book.isbn"), primary_key=True)
	ar_id = db.Column(db.Integer, primary_key=True)
	rid = db.Column(db.Integer, db.ForeignKey("resource.rid")) 

class OCRResourceModel(db.Model):
	__tablename__ = "ocr_resource_link"

	isbn = db.Column(db.Integer, db.ForeignKey("book.isbn"), primary_key=True)
	page = db.Column(db.Integer, primary_key=True)
	rid = db.Column(db.Integer, db.ForeignKey("resource.rid"))

class ResourceInstanceModel(db.Model):
	__tablename__ = "resource_instance"

	isbn = db.Column(db.Integer, db.ForeignKey("book.isbn"), primary_key=True)
	rid = db.Column(db.Integer, db.ForeignKey("resource.rid"), primary_key=True)


class BlobFormat(fields.Raw):
	def format(self, value):
		encoded_str = base64.b64encode(value).decode('ascii')
		return encoded_str


book_fields = {
    'isbn' : fields.Integer,
    'title' : fields.String,
    'author' : fields.String,
    'ar_blob' : BlobFormat,
    'ocr_blob' : fields.String,
    'edition' : fields.String,
    'author' : fields.String
}

resource_fields = {
	'rid' : fields.Integer,
	'url' : fields.String,
	'display' : fields.String,
	'downloadable' : fields.Boolean
}

publisher_fields = {
	'publisher' : fields.String,
	'email' : fields.String
}


resource_list_fields = {
	'resources' : fields.List(fields.Nested(resource_fields), attribute="resources")
}

book_list_fields = {
	'books' : fields.List(fields.Nested(book_fields), attribute="books")
}

search_result_fields = {
	'title' : fields.String,
	'isbn' : fields.Integer
}

search_result_list_fields = {
	'search_results' : fields.List(fields.Nested(search_result_fields), attribute="results")
}

all_fields = {
	'basic_info' : fields.Nested(book_fields),
	'ar_resources' : fields.List(fields.Nested(resource_fields), attribute="ar_items"),
	'ocr_resources' : fields.List(fields.Nested(resource_fields), attribute="ocr_items"),
	'publisher_info' : fields.Nested(publisher_fields)
}

def abort_if_invalid(missing_resource):
	abort(404, message="Invalid " + missing_resource)

class Book(Resource):
	@marshal_with(all_fields)
	def get(self, isbn):
		#retrieve all information about books
		basic_result = BookModel.query.get(int(isbn))
		if not basic_result:
			abort_if_invalid("ISBN")
		publisher_info = db.session.query(PublisherModel).join(BookModel, BookModel.publisher == PublisherModel.publisher).filter(BookModel.isbn == isbn).first()
		ar_result = db.session.query(ResourceModel).join(ARResourceModel, ARResourceModel.rid == ResourceModel.rid).filter(ARResourceModel.isbn == isbn).order_by(ARResourceModel.ar_id).all()
		ocr_result = db.session.query(ResourceModel).join(OCRResourceModel, OCRResourceModel.rid == ResourceModel.rid).filter(OCRResourceModel.isbn == isbn).order_by(OCRResourceModel.page).all()
		return { 'basic_info' : basic_result, 'ar_items' : ar_result, 'ocr_items' : ocr_result, 'publisher_info' : publisher_info}, 201


class TitleMatch(Resource):
	def get(self, title):
		#retrieve list of all titles
		titles = db.session.query(BookModel.title, BookModel.isbn).all()
		titles_list = [[t[0], t[1], 0] for t in titles]
		results = match(title.lower(), titles_list)
		query_results = []
		for res in results:
			q = BookModel.query.get(res[1])
			query_results.append([q.title, res[1]])
		return {'results' : query_results}, 201

class AllResources(Resource):
	@marshal_with(resource_list_fields)
	def get(self, isbn):
		#retrieve all book resources based on ISBN
		all_resources = db.session.query(ResourceModel).join(ResourceInstanceModel, ResourceModel.rid == ResourceInstanceModel.rid).join(BookModel, BookModel.isbn == ResourceInstanceModel.isbn).filter(BookModel.isbn == isbn).order_by(ResourceModel.rid).all()
		if not all_resources:
			abort_if_invalid("ISBN")
		return {'resources' : all_resources}

class BookResource(Resource):
	@marshal_with(resource_fields)
	def get(self, rid):
		#if needed, get specific rid
		resource_result = ResourceModel.query.get(rid)
		if not resource_result:
			abort_if_invalid("resource id")
		return resource_result

class AllBooks(Resource):
	@marshal_with(book_list_fields)
	def get(self):
		books = db.session.query(BookModel).all()
		return {'books' : books}


api.add_resource(AllResources, "/books/resources/<int:isbn>") #gets all resources for a particular book
api.add_resource(TitleMatch, "/titles/<title>") #gets possible string matches for a book
api.add_resource(BookResource, "/resources/<int:rid>") #gets a particular resource
api.add_resource(AllBooks, "/books") #gets a list of all books
api.add_resource(Book, "/books/<int:isbn>") #gets all information about a book


if __name__ == '__main__':
	app.run(host="0.0.0.0")
